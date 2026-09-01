<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Token\RevokeTokenRequest;
use App\Http\Requests\Token\RotateTokenRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class TokenController extends Controller
{
    /**
     * Display a listing of user's tokens.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tokens = $user->tokens()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Daftar token berhasil diambil',
            'data' => $tokens->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'created_at' => $token->created_at,
                    'expires_at' => $token->expires_at,
                    'last_used_at' => $token->last_used_at,
                    'ip_address' => $token->ip_address,
                    'user_agent' => $token->user_agent,
                    'is_expired' => $token->expires_at && $token->expires_at->isPast(),
                ];
            }),
        ]);
    }

    /**
     * Display the specified token details.
     */
    public function show(Request $request, int $tokenId): JsonResponse
    {
        $user = $request->user();
        $token = $user->tokens()->find($tokenId);

        if (! $token) {
            return response()->json([
                'message' => 'Token tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail token berhasil diambil',
            'data' => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'created_at' => $token->created_at,
                'expires_at' => $token->expires_at,
                'last_used_at' => $token->last_used_at,
                'ip_address' => $token->ip_address,
                'user_agent' => $token->user_agent,
                'is_expired' => $token->expires_at && $token->expires_at->isPast(),
            ],
        ]);
    }

    /**
     * Revoke the specified token.
     */
    public function revoke(RevokeTokenRequest $request): JsonResponse
    {
        $user = $request->user();
        $tokenId = $request->validated('token_id');
        $token = $user->tokens()->find($tokenId);

        if (! $token) {
            return response()->json([
                'message' => 'Token tidak ditemukan',
            ], 404);
        }

        // Prevent revoking the token currently being used
        $currentToken = $user->currentAccessToken();
        if ($currentToken && $currentToken->id === $tokenId) {
            return response()->json([
                'message' => 'Tidak dapat mencabut token yang sedang aktif. Gunakan endpoint rotate untuk membuat token baru.',
            ], 400);
        }

        $token->delete();

        activity('token')
            ->causedBy($user)
            ->withProperties([
                'token_id' => $tokenId,
                'token_name' => $token->name,
                'action' => 'revoke',
            ])
            ->log('Token API dicabut');

        return response()->json([
            'message' => 'Token berhasil dicabut',
        ]);
    }

    /**
     * Rotate the specified token (revoke and create new one).
     */
    public function rotate(RotateTokenRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $tokenId = $validated['token_id'];
        $tokenName = $validated['token_name'] ?? 'rotated_token';

        $oldToken = $user->tokens()->find($tokenId);

        if (! $oldToken) {
            return response()->json([
                'message' => 'Token tidak ditemukan',
            ], 404);
        }

        // Get old token abilities
        $abilities = $oldToken->abilities ?? ['*'];

        // Create new token with same abilities
        $newToken = $user->createToken($tokenName, $abilities);

        // Update metadata for new token using forceFill since ip_address and user_agent are not fillable
        $newTokenModel = PersonalAccessToken::find($newToken->accessToken->id);
        $newTokenModel->forceFill([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expires_at' => now()->addMinutes(config('sanctum.expiration')),
        ]);
        $newTokenModel->save();

        // Revoke old token
        $oldToken->delete();

        activity('token')
            ->causedBy($user)
            ->withProperties([
                'old_token_id' => $tokenId,
                'old_token_name' => $oldToken->name,
                'new_token_name' => $tokenName,
                'action' => 'rotate',
            ])
            ->log('Token API dirotasi');

        return response()->json([
            'message' => 'Token berhasil dirotasi',
            'data' => [
                'access_token' => $newToken->plainTextToken,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration') * 60, // in seconds
            ],
        ]);
    }

    /**
     * Revoke all user's tokens except current one.
     */
    public function revokeAll(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        // Delete all tokens except current
        $deletedCount = $user->tokens()
            ->when($currentToken, function ($query) use ($currentToken) {
                return $query->where('id', '!=', $currentToken->id);
            })
            ->delete();

        activity('token')
            ->causedBy($user)
            ->withProperties([
                'deleted_count' => $deletedCount,
                'action' => 'revoke_all',
            ])
            ->log('Semua token API dicabut');

        return response()->json([
            'message' => "Berhasil mencabut {$deletedCount} token",
        ]);
    }

    /**
     * Revoke all tokens including current one (logout from all devices).
     */
    public function revokeAllIncludingCurrent(Request $request): JsonResponse
    {
        $user = $request->user();
        $deletedCount = $user->tokens()->delete();

        activity('token')
            ->causedBy($user)
            ->withProperties([
                'deleted_count' => $deletedCount,
                'action' => 'revoke_all_including_current',
            ])
            ->log('Semua token API dicabut termasuk token saat ini');

        return response()->json([
            'message' => "Berhasil mencabut {$deletedCount} token. Anda akan logout setelah response ini.",
        ]);
    }
}
