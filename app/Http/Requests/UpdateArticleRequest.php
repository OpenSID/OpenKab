<?php

namespace App\Http\Requests;

use App\Models\CMS\Article;
use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
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
        $rules = Article::$rules;

        return $rules;
    }

    public function messages()
    {
        return Article::$errorMessages;
    }
}
