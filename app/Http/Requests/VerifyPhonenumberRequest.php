<?php

namespace App\Http\Requests;

use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;

class VerifyPhonenumberRequest extends FormRequest
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
            'phonenumber' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/phonenumber.php'))['phonenumber_not_unique'],
            'code' => ['required', 'integer', 'numeric', 'min:100000', 'max:999999'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
