<?php

namespace App\Http\Requests\Accounts;

use App\Auth\CheckAuthentication;
use App\Http\Requests\BaseFormRequest;
use App\Models\Role;
use App\Models\User;
use App\Rules\ProhibitExtraFeilds;

class IndexAccountsRequest extends BaseFormRequest
{
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

        $retrieveUser = $user->authenticatableRole->role->role->retrieveUserSubjects;

        foreach ($retrieveUser as $retrieveUserModel) {
            if ($retrieveUserModel->object !== null && $retrieveUserModel->relatedObject->childRoleModel->roleName->name === $input['roleName']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $array = [
            'roleName' => (include(base_path() . '/app/Rules/BuiltInRules/Models/role.php'))['roleName'],
            'lastAccountId' => ['nullable', 'integer', 'numeric', 'min:1'],
            'count' => ['required', 'integer', 'numeric']
        ];
        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));
        return $array;
    }
}
