<?php

namespace App\Http\Requests;

use App\Http\Requests\Accounts\StorePatientAccountRequest;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
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
        $array = (include(base_path() . '/app/Rules/BuiltInRules/Models/User/rules.php'));

        unset($array['role']);

        $specialRules = (include(base_path() . '/app/Rules/BuiltInRules/Models/Patient/rules.php'));
        unset($specialRules['laser_grade']);

        $array = array_merge($array, $specialRules);

        array_unshift($rules[array_key_first($rules)], new ProhibitExtraFeilds($rules));

        return $array;
    }
}
