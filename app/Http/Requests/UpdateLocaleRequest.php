<?php

namespace App\Http\Requests;

use App\Rules\LangExists;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLocaleRequest extends FormRequest
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
        return [
            'locale' => ['required', 'string', 'bail', 'regex:/\A[a-zA-Z]+\z/', new LangExists]
        ];
    }
}
