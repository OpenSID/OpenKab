<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RefreshTokenRequest;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class RefreshTokenController extends Controller
{
    /**
     * Refresh access token using refresh token.
     *
     * @param RefreshTokenRequest $request
     * @return JsonResponse
     */
    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $refreshTokenString = $request->validated('refresh_token');

            // Find the refresh token
            $refreshToken = RefreshToken::where('refresh_token', $refreshTokenString)->first();

            if (! $refreshToken) {
                return response()->json([
                    'message' => 'Refresh token tidak ditemukan',
                ], JsonResponse::HTTP_NOT_FOUND);
            }

            // Check if refresh token is valid
            if (! $refreshToken->isValid()) {
                if ($refreshToken->is_revoked) {
                    return response()->json([
                        'message' => 'Refresh token telah dicabut. Silakan login ulang.',
                    ], JsonResponse::HTTP_FORBIDDEN);
                }

                if ($refreshToken->isExpired()) {
                    return response()->json([
                        'message' => 'Refresh token telah expired. Silakan login ulang.',
                    ], JsonResponse::HTTP_FORBIDDEN);
                }
            }

            // Get the user
            $user = $refreshToken->user;

            if (! $user || ! $user->active) {
                return response()->json([
                    'message' => 'User tidak ditemukan atau tidak aktif',
                ], JsonResponse::HTTP_FORBIDDEN);
            }

            // Revoke old refresh token (single use)
            $refreshToken->revoke('token_refresh');

            // Revoke old access token if exists
            if ($refreshToken->access_token_id) {
                PersonalAccessToken::find($refreshToken->access_token_id)?->delete();
            }

            // Create new access token
            $newAccessToken = $user->createToken('auth_token');

            // Update access token with metadata
            $newAccessTokenModel = PersonalAccessToken::find($newAccessToken->accessToken->id);
            $newAccessTokenModel->forceFill([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'expires_at' => now()->addMinutes(config('sanctum.expiration')),
            ]);
            $newAccessTokenModel->save();

            // Create new refresh token
            $newRefreshToken = $this->createRefreshToken($user, $newAccessTokenModel->id, $request);

            return response()->json([
                'message' => 'Token berhasil di-refresh',
                'data' => [
                    'access_token' => $newAccessToken->plainTextToken,
                    'refresh_token' => $newRefreshToken->refresh_token,
                    'token_type' => 'Bearer',
                    'expires_in' => config('sanctum.expiration') * 60, // in seconds
                    'refresh_expires_in' => config('auth.refresh_token_lifetime', 2592000), // 30 days in seconds
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Refresh token error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Server Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a new refresh token.
     *
     * @param User $user
     * @param int $accessTokenId
     * @param Request $request
     * @return RefreshToken
     */
    private function createRefreshToken(User $user, int $accessTokenId, Request $request): RefreshToken
    {
        return RefreshToken::create([
            'user_id' => $user->id,
            'refresh_token' => Str::random(100),
            'access_token_id' => $accessTokenId,
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? 'Unknown',
            'expires_at' => now()->addDays(config('auth.refresh_token_lifetime_days', 30)),
            'is_revoked' => false,
        ]);
    }

    /**
     * Revoke refresh token (logout).
     *
     * @param RefreshTokenRequest $request
     * @return JsonResponse
     */
    public function revoke(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $refreshTokenString = $request->validated('refresh_token');
            $refreshToken = RefreshToken::where('refresh_token', $refreshTokenString)->first();

            if (! $refreshToken) {
                return response()->json([
                    'message' => 'Refresh token tidak ditemukan',
                ], JsonResponse::HTTP_NOT_FOUND);
            }

            // Revoke the refresh token
            $refreshToken->revoke('logout');

            // Revoke associated access token
            if ($refreshToken->access_token_id) {
                PersonalAccessToken::find($refreshToken->access_token_id)?->delete();
            }

            return response()->json([
                'message' => 'Berhasil logout',
            ]);
        } catch (\Exception $e) {
            Log::error('Revoke refresh token error: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Server Error: ' . $e->getMessage(),
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Revoke all refresh tokens for user (logout from all devices).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function revokeAll(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'User tidak terautentikasi',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Revoke all refresh tokens
        $count = $user->refreshTokens()->update([
            'is_revoked' => true,
            'revoked_at' => now(),
            'revoked_reason' => 'logout_all_devices',
        ]);

        // Revoke all access tokens
        $user->tokens()->delete();

        // Log activity
        activity('token')
            ->causedBy($user)
            ->withProperties([
                'revoked_count' => $count,
                'action' => 'revoke_all_refresh_tokens',
            ])
            ->log('Semua refresh token dicabut (logout dari semua perangkat)');

        return response()->json([
            'message' => "Berhasil logout dari {$count} perangkat",
        ]);
    }
}
