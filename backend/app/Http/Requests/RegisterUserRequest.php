<?php

namespace App\Http\Requests;

use App\Rules\ProhibitExtraFeilds;

class RegisterUserRequest extends BaseFormRequest
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
        $array['userAttributes'] = ['required', 'array', 'min:1'];
        $array['userAccountAttributes'] = ['required:userAttributes', 'array', 'min:1'];
        $array['avatar'] = (include(base_path() . '/app/Rules/BuiltInRules/Models/avatar.php'))['avatar_optional'];

        $rules = (include(base_path() . '/app/Rules/BuiltInRules/Models/User/rules.php'));
        unset($rules['password_confirmation'][array_search('same:password', $rules['password_confirmation'], true)]);
        $rules['password_confirmation'][] = 'same:userAttributes.password';

        foreach ($rules as $key => $value) {
            $array['userAttributes.' . $key] = $value;
        }

        foreach (include(base_path() . '/app/Rules/BuiltInRules/Models/Patient/rules.php') as $key => $value) {
            $array['userAccountAttributes.' . $key] = $value;
        }


        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }

    protected function passedValidation()
    {
        $tmp = $this->all();
        unset($tmp['userAttributes']['password_confirmation']);
        $this->replace($tmp);
    }
}
