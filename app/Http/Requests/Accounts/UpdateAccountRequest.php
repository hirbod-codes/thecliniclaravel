<?php

namespace App\Http\Requests\Accounts;

use App\Auth\CheckAuthentication;
use App\Http\Requests\BaseFormRequest;
use App\Models\User;
use App\Rules\ProhibitExtraFeilds;

class UpdateAccountRequest extends BaseFormRequest
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

        $accountId = intval(array_reverse(explode('/', $this->path()))[0]);
        if ($accountId === $user->getKey()) {
            $isSelf = true;
        } else {
            $isSelf = false;
        }
        /** @var User $user */
        $targetUser = User::query()->whereKey($accountId)->firstOrFail();
        $targetUserRoleName = $targetUser->authenticatableRole->role->roleName->name;
        $input = $this->safe()->all();

        $updateUserModels = $user->authenticatableRole->role->role->updateUserSubjects;
        foreach ($input as $key => $value) {
            foreach ($updateUserModels as $updateUserModel) {
                if ($updateUserModel->relatedColumn->name !== $key) {
                    continue;
                }

                if (($isSelf && $updateUserModel->object !== null) || (!$isSelf && ($updateUserModel->object === null || ($updateUserModel->object !== null && $updateUserModel->relatedObject->childRoleModel->roleName->name !== $targetUserRoleName)))) {
                    continue;
                }

                continue 2;
            }

            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $accountId = intval(array_reverse(explode('/', $this->path()))[0]);
        /** @var User $user */
        $user = User::query()->whereKey($accountId)->firstOrFail();

        $array = include(base_path() . '/app/Rules/BuiltInRules/Models/User/updateRules.php');

        $array = array_merge($array, include(base_path() . '/app/Rules/BuiltInRules/Models/' . class_basename($user->authenticatableRole) . '/updateRules.php'));

        if (isset($array['phonenumber'])) {
            unset($array['phonenumber']);
        }

        if (isset($array['password'])) {
            unset($array['password']);
        }

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
