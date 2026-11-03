<?php

namespace App\Http\Requests\Peta;

use App\Rules\ValidKodeDesa;
use App\Support\Coordinate;
use Illuminate\Foundation\Http\FormRequest;

class StoreLokasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:50'],
            'jenis' => ['required'],
            'kategori' => ['required'],
            'kode_desa' => ['required', new ValidKodeDesa],
            'lat' => ['required', 'numeric', 'between:'.Coordinate::MIN_LAT.','.Coordinate::MAX_LAT],
            'lng' => ['required', 'numeric', 'between:'.Coordinate::MIN_LNG.','.Coordinate::MAX_LNG],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama lokasi wajib diisi.',
            'jenis.required' => 'Jenis lokasi wajib dipilih.',
            'kategori.required' => 'Kategori lokasi wajib dipilih.',
            'kode_desa.required' => 'Desa wajib dipilih.',
            'lat.required' => 'Latitude wajib diisi.',
            'lat.between' => 'Latitude harus berada di wilayah Indonesia ('.Coordinate::MIN_LAT.' s.d. '.Coordinate::MAX_LAT.').',
            'lng.required' => 'Longitude wajib diisi.',
            'lng.between' => 'Longitude harus berada di wilayah Indonesia ('.Coordinate::MIN_LNG.' s.d. '.Coordinate::MAX_LNG.').',
        ];
    }
}
