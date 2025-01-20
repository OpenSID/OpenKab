<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Repository\SuplemenRepository;
use App\Http\Transformers\SuplemenTerdataTransformer;
use App\Http\Transformers\SuplemenTransformer;
use App\Models\Suplemen;
use App\Models\SuplemenTerdata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class SuplemenController extends Controller
{
    public function __construct(protected SuplemenRepository $suplemen)
    {
    }

    public function index()
    {
        $suplemenData = $this->suplemen->listSuplemen();

        return fractal($suplemenData, new SuplemenTransformer())
            ->addMeta(['message' => 'daftar suplemen'])
            ->respond();
    }

    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'sasaran' => 'required|in:1,2',
            'nama' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:300',
            'status' => 'required|in:0,1',
            'sumber' => 'nullable|string',
            'form_isian' => 'nullable',
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
            if ($request->form_isian == '[{"tipe":"","nama_kode":"","label_kode":"","deskripsi_kode":"","required":0,"kolom":"","atribut":"","pilihan_kode":"","referensi_kode":""}]' or $request->form_isian == '[]') {
                $request->form_isian = '';
            }

            $suplemen = Suplemen::create([
                'sasaran' => $request->sasaran,
                'nama' => $request->nama,
                'keterangan' => $request->keterangan,
                'status' => $request->status,
                'sumber' => $request->sumber,
                'form_isian' => $request->form_isian,
            ]);

            // Menyimpan pesan sukses ke session
            Session::flash('success', 'Data berhasil disimpan.');

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan.',
                'data' => $suplemen,
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
            'sasaran' => 'required|in:1,2',
            'nama' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:300',
            'status' => 'required|in:0,1',
            'sumber' => 'nullable|string',
            'form_isian' => 'nullable',
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
            $suplemen = Suplemen::findOrFail($id);

            // Jika form_isian kosong atau hanya data kosong, set null
            if ($request->form_isian == '[{"tipe":"","nama_kode":"","label_kode":"","deskripsi_kode":"","required":0,"kolom":"","atribut":"","pilihan_kode":"","referensi_kode":""}]' or $request->form_isian == '[]') {
                $request->merge(['form_isian' => null]);
            }

            // Update data di database
            $suplemen->update([
                'sasaran' => $request->sasaran,
                'nama' => $request->nama,
                'keterangan' => $request->keterangan,
                'status' => $request->status,
                'sumber' => $request->sumber,
                'form_isian' => $request->form_isian,
            ]);

            // Menyimpan pesan sukses ke session
            Session::flash('success', 'Data berhasil diperbarui.');

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui.',
                'data' => $suplemen,
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

    public function sasaran()
    {
        try {
            $sasaran = unserialize(SASARAN);
            if (! is_array($sasaran)) {
                throw new \Exception('Invalid SASARAN format.');
            }

            return response()->json([
                'success' => true,
                'data' => array_map(fn ($value, $key) => ['id' => $key, 'nama' => $value], $sasaran, array_keys($sasaran)),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses SASARAN.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function status()
    {
        try {
            $status = unserialize(STATUS_SUPLEMEN);
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

    public function destroy($id)
    {
        try {
            Suplemen::where('id', $id)->delete();

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
            SuplemenTerdata::whereIn('id', $request->ids)->delete();

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

    public function detail($sasaran, $id)
    {
        $suplemenData = $this->suplemen->listSuplemenTerdata($sasaran, $id);

        return fractal($suplemenData, new SuplemenTerdataTransformer())
            ->addMeta(['message' => 'daftar suplemen'])
            ->respond();
    }
}
