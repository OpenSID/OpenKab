<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\SettingRepository;
use App\Http\Transformers\SettingTransformer;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class SettingController extends Controller
{
    public function __construct(protected SettingRepository $setting)
    {
    }

    public function index()
    {
        return $this->fractal($this->setting->listsetting(), new SettingTransformer(), 'daftar setting')->respond();
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            // Ambil semua key yang valid dari database
            $validKeys = Setting::pluck('key')->toArray();

            foreach ($request->all() as $key => $value) {
                // Jika key tidak ditemukan di database, kembalikan error
                if (! in_array($key, $validKeys)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Key '{$key}' tidak ditemukan dalam pengaturan.",
                    ], Response::HTTP_BAD_REQUEST);
                }

                // Jika key adalah 'opendk_api_key', lakukan aksi khusus
                if ($key === 'opendk_api_key') {
                    $this->removeTokenSynchronize($value);
                }

                // Lakukan update pada setting yang sesuai
                Setting::where('key', $key)->update(['value' => $value]);
            }

            return response()->json([
                'success' => true,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function removeTokenSynchronize($token)
    {
        $user = User::whereUsername('synchronize')->first();
        $excludeToken = PersonalAccessToken::findToken($token);
        $user->tokens()->where('id', '!=', $excludeToken->id)->delete();
    }
}
