<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\SettingRepository;
use App\Http\Transformers\SettingTransformer;
use App\Models\Setting;
use Illuminate\Http\Request;
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
            // Ambil daftar key yang valid dari database
            $validKeys = Setting::pluck('key')->toArray();

            foreach ($request->all() as $key => $value) {
                // Jika key tidak ada di database, kembalikan error 422
                if (! in_array($key, $validKeys)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Key '{$key}' tidak ditemukan.",
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                // Update setting
                Setting::where('key', $key)->update(['value' => $value]);
                activity('data-log')->event('updated')->withProperties($request)->log('Setting Aplikasi');
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
}
