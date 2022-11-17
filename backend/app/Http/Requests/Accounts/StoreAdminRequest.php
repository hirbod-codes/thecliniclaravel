<?php

namespace App\Http\Requests\Accounts;

use App\Auth\CheckAuthentication;
use App\Helpers\TraitAuthResolver;
use App\Http\Requests\BaseFormRequest;
use App\Models\User;
use App\Rules\ProhibitExtraFeilds;
use App\Rules\UniqueFullName;

class StoreAdminRequest extends BaseFormRequest
{
    use TraitAuthResolver;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        /** @var User $user */
        $user = (new CheckAuthentication)->getAuthenticated();
        $input = $this->safe()->all();

        $createUser = $user->authenticatableRole->role->role->createUserSubjects;

        foreach ($createUser as $createUserModel) {
            if ($createUserModel->object !== null && $createUserModel->relatedObject->childRoleModel->roleName->name === $input['roleName']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = (include(base_path() . '/app/Rules/BuiltInRules/Models/User/rules.php'));
        $specialRules = include(base_path() . '/app/Rules/BuiltInRules/Models/Admin/rules.php');

        $array['roleName'] = (include(base_path() . '/app/Rules/BuiltInRules/Models/role.php'))['roleName'];
        $array['userAttributes'] = ['required', 'array'];
        if (!empty($specialRules)) {
            $array['userAccountAttributes'] = ['required', 'array'];
        }

        unset($rules['password_confirmation'][array_search('same:password', $rules['password_confirmation'], true)]);
        $rules['password_confirmation'][] = 'same:userAttributes.password';

        foreach ($rules as $key => $value) {
            if (in_array($key, ['phonenumber', 'password'])) {
                continue;
            }

            $array['userAttributes.' . $key] = $value;
        }

        foreach ($specialRules as $key => $value) {
            $array['userAccountAttributes.' . $key] = $value;
        }

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));
        array_unshift($array[array_key_first($array)], new UniqueFullName('userAttributes.firstname', 'userAttributes.lastname'));

        return $array;
    }

    protected function prepareForValidation()
    {
        $this->replace(array_merge($this->all(), ['roleName' => class_basename($this->path())]));
    }

    protected function passedValidation()
    {
        $tmp = $this->all();
        unset($tmp['userAttributes']['password_confirmation']);
        $this->replace($tmp);
    }
}
