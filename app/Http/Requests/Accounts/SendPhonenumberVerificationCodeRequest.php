<?php

namespace App\Http\Requests\Accounts;

use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;

class SendPhonenumberVerificationCodeRequest extends FormRequest
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
            'phonenumber' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/phonenumber.php'))['phonenumber']
        ];
        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));
        return $array;
    }
}
