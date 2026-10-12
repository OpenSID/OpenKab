<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArtikelImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'file' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'file.required' => 'File gambar harus diunggah.',
            'file.image' => 'File harus berupa gambar.',
            'file.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau GIF.',
            'file.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
            'file.valid_file' => 'File gambar tidak valid atau mengandung konten yang berbahaya.',
        ];
    }
}
