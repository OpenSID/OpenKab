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

        // Generate unique key for this request based on IP
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
     * Resolve request signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature(Request $request): string
    {
        // Use IP address as the signature for global rate limiting
        return sha1(
            'global-rate-limit:' . $request->ip()
        );
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
}