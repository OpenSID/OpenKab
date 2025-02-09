<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Repository\PointRepository;
use App\Http\Transformers\PointTransformer;
use App\Models\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PointController extends Controller
{
    public function __construct(protected PointRepository $point)
    {
    }

    public function index()
    {
        $pointData = $this->point->listPoint();

        return fractal($pointData, new PointTransformer())
            ->addMeta(['message' => 'daftar tipe lokasi'])
            ->respond();
    }

    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'simbol' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Simpan data ke database
        try {
            $point = Point::create([
                'nama' => $request->nama,
                'simbol' => $request->simbol ?? 'default.png',
                'parrent' => $request->parrent,
                'tipe' => $request->tipe,
                'sumber' => $request->sumber,
            ]);

            // Menyimpan pesan sukses ke session
            Session::flash('success', 'Data berhasil disimpan.');

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan.',
                'data' => $point,
            ], 201);
        } catch (\Exception $e) {
            // Menyimpan pesan error ke session
            Session::flash('error', 'Terjadi kesalahan saat menyimpan data.');

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'simbol' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Cari data berdasarkan ID
            $point = Point::findOrFail($id);

            // Update data di database
            $point->update([
                'nama' => $request->nama,
                'simbol' => $request->simbol ?? $point->simbol,
            ]);

            // Menyimpan pesan sukses ke session
            Session::flash('success', 'Data berhasil diperbarui.');

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui.',
                'data' => $point,
            ], 200);
        } catch (\Exception $e) {
            // Menyimpan pesan error ke session
            Session::flash('error', 'Terjadi kesalahan saat memperbarui data.');

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Point::where('id', $id)->delete();
            Point::where('parrent', $id)->delete();

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

    public function delete_multiple(Request $request)
    {
        try {
            // Hapus data yang terpilih
            Point::whereIn('id', $request->ids)->delete();
            Point::whereIn('parrent', $request->ids)->delete();

            return response()->json([
                'success' => true,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            // report($e);

            return response()->json([
                'success' => false,
                'message' => '',
                // 'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function lock($id, $val = 1)
    {
        try {
            Point::findOrFail($id)->update(['enabled' => $val]);

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

    public function detail($id)
    {
        $pointData = $this->point->listSubPoint($id);

        return fractal($pointData, new PointTransformer())
            ->addMeta(['message' => 'daftar tipe lokasi'])
            ->respond();
    }

    public function status()
    {
        try {
            $status = [
                Point::LOCK => 'Aktif',
                Point::UNLOCK => 'Tidak Aktif',
            ];
            if (! is_array($status)) {
                throw new \Exception('Invalid STATUS_SUPLEMEN format.');
            }

            return response()->json([
                'success' => true,
                'data' => array_map(fn ($value, $key) => ['id' => $key, 'nama' => $value], $status, array_keys($status)),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses STATUS_SUPLEMEN.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
