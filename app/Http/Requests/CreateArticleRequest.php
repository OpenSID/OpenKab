<?php

namespace App\Http\Requests;

use App\Models\CMS\Article;
use Illuminate\Foundation\Http\FormRequest;

class CreateArticleRequest extends FormRequest
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
        return Article::$rules;
    }

    public function messages()
    {
        return Article::$errorMessages;
    }
}
