<?php

namespace App\Http\Requests;

use App\Models\CMS\Download;
use Illuminate\Foundation\Http\FormRequest;

class CreateDownloadRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        $rules = Download::$rules;
        $rules['download_file'] = 'required|'.$rules['download_file'];

        return $rules;
    }
}
