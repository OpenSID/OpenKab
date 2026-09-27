<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class DetectTokenAnomaly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $currentToken = $user->currentAccessToken();

        if (! $currentToken) {
            return $next($request);
        }

        $currentIp = $request->ip();
        $currentUserAgent = $request->userAgent();
        $storedIp = $currentToken->ip_address;
        $storedUserAgent = $currentToken->user_agent;

        $anomalies = [];

        // Check for IP address change
        if ($storedIp && $storedIp !== $currentIp) {
            $anomalies[] = 'ip_address_changed';
            Log::warning('Token IP anomaly detected', [
                'user_id' => $user->id,
                'username' => $user->username ?? $user->email,
                'token_id' => $currentToken->id,
                'token_name' => $currentToken->name,
                'original_ip' => $storedIp,
                'current_ip' => $currentIp,
                'request_path' => $request->path(),
            ]);
        }

        // Check for User Agent change (different device/browser)
        if ($storedUserAgent && $storedUserAgent !== $currentUserAgent) {
            $anomalies[] = 'user_agent_changed';
            Log::warning('Token User Agent anomaly detected', [
                'user_id' => $user->id,
                'username' => $user->username ?? $user->email,
                'token_id' => $currentToken->id,
                'token_name' => $currentToken->name,
                'original_user_agent' => substr($storedUserAgent, 0, 200),
                'current_user_agent' => substr($currentUserAgent, 0, 200),
                'request_path' => $request->path(),
            ]);
        }

        // If anomalies detected, update token metadata and log activity
        if (! empty($anomalies)) {
            // Update the token's IP and user agent to current values using forceFill
            $currentToken->forceFill([
                'ip_address' => $currentIp,
                'user_agent' => $currentUserAgent,
            ]);
            $currentToken->save();

            // Log activity for security audit
            try {
                activity('token_anomaly')
                    ->causedBy($user)
                    ->withProperties([
                        'token_id' => $currentToken->id,
                        'token_name' => $currentToken->name,
                        'anomalies' => $anomalies,
                        'original_ip' => $storedIp,
                        'current_ip' => $currentIp,
                        'original_user_agent' => $storedUserAgent,
                        'current_user_agent' => $currentUserAgent,
                    ])
                    ->log('Anomali penggunaan token terdeteksi');
            } catch (\Exception $e) {
                // Log to Laravel log if activity log fails
                Log::error('Failed to log token anomaly activity', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'token_id' => $currentToken->id,
                ]);
            }
        }

        return $next($request);
    }
}
