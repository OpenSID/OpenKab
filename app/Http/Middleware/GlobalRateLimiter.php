<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class GlobalRateLimiter
{
    /**
     * The rate limiter instance.
     *
     * @var \Illuminate\Cache\RateLimiter
     */
    protected $limiter;

    /**
     * Create a new rate limiter middleware instance.
     *
     * @param  \Illuminate\Cache\RateLimiter  $limiter
     * @return void
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if global rate limiter is enabled
        if (!config('rate-limiter.enabled', false)) {
            return $next($request);
        }

        // Check if current IP should be excluded
        if ($this->shouldExcludeIp($request)) {
            return $next($request);
        }

        // Check if current path should be excluded
        if ($this->shouldExcludePath($request)) {
            return $next($request);
        }

        // Get configuration from .env or use defaults
        $maxAttempts = config('rate-limiter.max_attempts', 60);
        $decayMinutes = config('rate-limiter.decay_minutes', 1);

        // Generate unique key for this request based on IP + User-Agent fingerprint + User ID (if authenticated)
        $key = $this->resolveRequestSignature($request);

        // Check if the request limit has been exceeded
        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildResponse($key, $maxAttempts);
        }

        // Add hit to the limiter
        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Add headers to the response
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - $this->limiter->attempts($key)));
        $response->headers->set('X-RateLimit-Reset', $this->limiter->availableIn($key));

        return $response;
    }

    /**
     * Resolve request signature using multiple factors.
     * 
     * Combines:
     * - IP address
     * - User-Agent browser fingerprint
     * - User ID (if authenticated)
     * 
     * This prevents bypass via VPN/IP rotation alone.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $components = [];
        
        // IP address component
        $components[] = $request->ip() ?? 'unknown-ip';
        
        // User-Agent fingerprint component (hash to avoid special chars)
        $userAgent = $request->userAgent() ?? 'unknown-ua';
        $components[] = $this->fingerprintUserAgent($userAgent);
        
        // User ID component (if authenticated)
        if ($request->user()) {
            $components[] = 'user:' . $request->user()->getAuthIdentifier();
        }
        
        // Combine all components and hash
        $signature = implode('|', $components);
        
        return sha1('global-rate-limit:' . $signature);
    }

    /**
     * Create a browser fingerprint from User-Agent string.
     * 
     * Extracts key browser/platform information to create a consistent fingerprint.
     *
     * @param  string  $userAgent
     * @return string
     */
    protected function fingerprintUserAgent(string $userAgent): string
    {
        // Hash the full user agent for consistency and to avoid special characters
        return hash('xxh64', $userAgent);
    }

    /**
     * Create a 'too many attempts' response.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function buildResponse(string $key, int $maxAttempts): Response
    {
        $seconds = $this->limiter->availableIn($key);
        $request = request();

        if (App::runningInConsole() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'status' => 'error',
                'code' => 429,
                'retry_after' => $seconds,
            ], 429);
        }

        return response('Too Many Attempts.', 429, [
            'Retry-After' => $seconds,
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
            'X-RateLimit-Reset' => $seconds,
        ]);
    }

    /**
     * Determine if the request IP should be excluded from rate limiting.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldExcludeIp(Request $request): bool
    {
        $excludeIps = config('rate-limiter.exclude_ips', []);
        
        return in_array($request->ip(), $excludeIps);
    }

    /**
     * Determine if the request path should be excluded from rate limiting.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldExcludePath(Request $request): bool
    {
        $excludePaths = config('rate-limiter.exclude_paths', []);
        $requestPath = $request->path();

        foreach ($excludePaths as $path) {
            if ($this->pathMatches($path, $requestPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the path matches the pattern.
     *
     * @param  string  $pattern
     * @param  string  $path
     * @return bool
     */
    protected function pathMatches(string $pattern, string $path): bool
    {
        // Convert wildcard pattern to regex
        $pattern = preg_quote($pattern, '#');
        $pattern = str_replace('\*', '.*', $pattern);

        return preg_match("#^{$pattern}$#", $path);
    }

    /**
     * Calculate progressive delay based on attempt count.
     * 
     * After each failed attempt, the delay increases exponentially.
     * Formula: base_delay * (multiplier ^ (attempts - 1))
     * 
     * Example with base=2s, multiplier=2:
     * - Attempt 1: 2s
     * - Attempt 2: 4s
     * - Attempt 3: 8s
     * - Attempt 4: 16s
     * - Attempt 5: 32s
     *
     * @param  int  $attempts
     * @return int Delay in seconds
     */
    public function calculateProgressiveDelay(int $attempts = 1): int
    {
        $baseSeconds = config('app.progressive_delay_base_seconds', 2);
        $multiplier = config('app.progressive_delay_multiplier', 2);
        
        // Calculate exponential delay: base * (multiplier ^ (attempts - 1))
        $delay = $baseSeconds * pow($multiplier, $attempts - 1);
        
        // Cap at 5 minutes (300 seconds) to prevent excessive delays
        return min($delay, 300);
    }

    /**
     * Record a failed authentication attempt for account lockout.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return array ['locked' => bool, 'delay' => int, 'attempts' => int]
     */
    public function recordFailedAttempt(string $key, int $maxAttempts = 5, int $decayMinutes = 15): array
    {
        $this->limiter->hit($key, $decayMinutes * 60);
        
        $attempts = $this->limiter->attempts($key);
        $isLocked = $attempts >= $maxAttempts;
        $delay = $this->calculateProgressiveDelay($attempts);
        
        return [
            'locked' => $isLocked,
            'delay' => $delay,
            'attempts' => $attempts,
            'remaining' => max(0, $maxAttempts - $attempts),
        ];
    }

    /**
     * Check if account is temporarily locked due to failed attempts.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return array ['locked' => bool, 'availableIn' => int]
     */
    public function isLocked(string $key, int $maxAttempts = 5): array
    {
        $isLocked = $this->limiter->tooManyAttempts($key, $maxAttempts);
        $availableIn = $isLocked ? $this->limiter->availableIn($key) : 0;
        
        return [
            'locked' => $isLocked,
            'availableIn' => $availableIn,
        ];
    }

    /**
     * Clear failed attempts for account lockout.
     *
     * @param  string  $key
     */
    public function clearFailedAttempts(string $key): void
    {
        $this->limiter->clear($key);
    }
}