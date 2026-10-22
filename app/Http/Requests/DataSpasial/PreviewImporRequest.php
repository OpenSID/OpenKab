<?php

declare(strict_types=1);

namespace App\Http\Requests\DataSpasial;

use Illuminate\Foundation\Http\FormRequest;

class PreviewImporRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'force_overwrite' => 'nullable|boolean',
            'mode' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
