<?php

namespace App\Http\Requests;

use App\Rules\CheckEnryptedValuesIds;
use Illuminate\Foundation\Http\FormRequest;

class ApiVerifyPhonenumberVerificationCodeRequest extends FormRequest
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
            'code_created_at_encrypted' => ['required', 'string', new CheckEnryptedValuesIds],
            'code_encrypted' => ['required', 'string'],

            'code' => ['required', 'string', 'numeric', 'size:6'],
        ];
    }
}
