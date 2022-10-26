<?php

namespace App\Http\Requests;

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
        $array['userAttributes'] = ['required_without:userAccountAttributes', 'array', 'min:1'];
        $array['userAccountAttributes'] = ['required_without:userAttributes', 'array', 'min:1'];
        $array['avatar'] = (include(base_path() . '/app/Rules/BuiltInRules/Models/avatar.php'))['avatar_optional'];

        foreach ((include(base_path() . '/app/Rules/BuiltInRules/Models/User/rules.php')) as $key => $value) {
            if (in_array($key, ['phonenumber', 'password'])) {
                continue;
            }

            $array['userAttributes.' . $key] = $value;
        }

        foreach (include(base_path() . '/app/Rules/BuiltInRules/Models/Patient/rules.php') as $key => $value) {
            $array['userAccountAttributes.' . $key] = $value;
        }


        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
