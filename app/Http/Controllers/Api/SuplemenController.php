<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Suplemen;
use Illuminate\Http\Request; // Sesuaikan nama model Anda
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SuplemenController extends Controller
{
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
            if ($request->form_isian == '[{"tipe":"","nama_kode":"","label_kode":"","deskripsi_kode":"","required":0,"kolom":"","atribut":"","pilihan_kode":"","referensi_kode":""}]') {
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
}
