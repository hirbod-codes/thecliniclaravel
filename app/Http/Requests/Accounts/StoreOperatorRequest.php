<?php

namespace App\Http\Requests\Accounts;

use App\Auth\CheckAuthentication;
use App\Helpers\TraitAuthResolver;
use App\Http\Requests\BaseFormRequest;
use App\Rules\ProhibitExtraFeilds;

class StoreOperatorRequest extends BaseFormRequest
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

        $createUser = $user->authenticatableRole->role->role->createUserSubjects;

        foreach ($createUser as $createUserModel) {
            if ($createUserModel->object !== null && $createUserModel->relatedObject->childRoleModel->roleName->name === class_basename($this->path())) {
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
        $array['userAttributes'] = ['required_without:userAccountAttributes', 'array', 'min:1'];
        $array['userAccountAttributes'] = ['required_without:userAttributes', 'array', 'min:1'];
        $array['avatar'] = (include(base_path() . '/app/Rules/BuiltInRules/Models/avatar.php'))['avatar_optional'];

        foreach ((include(base_path() . '/app/Rules/BuiltInRules/Models/User/rules.php')) as $key => $value) {
            if (in_array($key, ['phonenumber', 'password'])) {
                continue;
            }

            $array['userAttributes.' . $key] = $value;
        }

        foreach (include(base_path() . '/app/Rules/BuiltInRules/Models/Operator/rules.php') as $key => $value) {
            $array['userAccountAttributes.' . $key] = $value;
        }

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
