<?php

namespace App\Http\Requests\Accounts;

use App\Auth\CheckAuthentication;
use App\Helpers\TraitAuthResolver;
use App\Http\Requests\BaseFormRequest;
use App\Models\User;
use App\Rules\ProhibitExtraFeilds;

class StoreDoctorRequest extends BaseFormRequest
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
        $array['roleName'] = (include(base_path() . '/app/Rules/BuiltInRules/Models/role.php'))['roleName'];
        $array['userAttributes'] = ['required', 'array'];
        $array['userAccountAttributes'] = ['required', 'array'];
        $array['avatar'] = (include(base_path() . '/app/Rules/BuiltInRules/Models/avatar.php'))['avatar_optional'];

        foreach ((include(base_path() . '/app/Rules/BuiltInRules/Models/User/rules.php')) as $key => $value) {
            if (in_array($key, ['phonenumber', 'password'])) {
                continue;
            }

            $array['userAttributes.' . $key] = $value;
        }

        foreach (include(base_path() . '/app/Rules/BuiltInRules/Models/Doctor/rules.php') as $key => $value) {
            $array['userAccountAttributes.' . $key] = $value;
        }

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }

    protected function prepareForValidation()
    {
        $this->replace(array_merge($this->all(), ['roleName' => class_basename($this->path())]));
    }
}
