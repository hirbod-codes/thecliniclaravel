<?php

namespace App\Http\Requests\Accounts;

use App\Rules\CheckEnryptedValuesIds;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;

class isPhonenumberVerificationCodeVerified extends FormRequest
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
        $array = [
            'code' => ['required', 'string', 'regex:/\A[0-9]{6}\z/'],
            'code_created_at_encrypted' => ['required', 'string'],
            'code_encrypted' => ['required', 'string'],
            'phonenumber_encrypted' => ['required', 'string', new CheckEnryptedValuesIds],
            'phonenumber' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/phonenumber.php'))['phonenumber'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));
        return $array;
    }
}
