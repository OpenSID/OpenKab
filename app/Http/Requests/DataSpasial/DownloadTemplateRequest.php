<?php

declare(strict_types=1);

namespace App\Http\Requests\DataSpasial;

use Illuminate\Foundation\Http\FormRequest;

class DownloadTemplateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sektor' => 'required|string',
            'kecamatan' => 'nullable|string',
            'periode' => 'nullable|date_format:Y',
        ];
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
