<?php

declare(strict_types=1);

namespace App\Http\Requests\DataSpasial;

use Illuminate\Foundation\Http\FormRequest;

class UploadImporRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx|max:10240',
            'sektor' => 'required|string',
        ];
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
