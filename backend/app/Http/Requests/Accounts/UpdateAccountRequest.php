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
        $input = $this->safe()->all();
        /** @var User $user */
        $user = (new CheckAuthentication)->getAuthenticated();

        $accountId = intval(array_reverse(explode('/', $this->path()))[0]);
        /** @var User $targetUser */
        $targetUser = User::query()->whereKey($accountId)->firstOrFail();

        $isSelf = $user->getKey() === $targetUser->getKey();

        $updateUserModels = $user->authenticatableRole->role->role->updateUserSubjects;

        if (isset($input['userAttributes']) && isset($input['userAccountAttributes'])) {
            $input = array_merge($input['userAttributes'], $input['userAccountAttributes']);
        } elseif (isset($input['userAttributes'])) {
            $input = $input['userAttributes'];
        } else {
            $input = $input['userAccountAttributes'];
        }

        foreach ($input as $key => $value) {
            foreach ($updateUserModels as $updateUserModel) {
                if ($updateUserModel->relatedColumn->name !== $key) {
                    continue;
                }

                if (($isSelf && $updateUserModel->object !== null) || (!$isSelf && ($updateUserModel->object === null || ($updateUserModel->object !== null && $updateUserModel->relatedObject->getKey() !== $targetUser->authenticatableRole->role->role->getKey())))) {
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

        $userUpdateRuels = include(base_path() . '/app/Rules/BuiltInRules/Models/User/updateRules.php');
        $userAccountUpdateRuels = include(base_path() . '/app/Rules/BuiltInRules/Models/' . class_basename($user->authenticatableRole) . '/updateRules.php');

        $array['userAttributes'] = ['required_without:userAccountAttributes', 'array', 'min:1', 'max:' . count($userUpdateRuels), function ($a, $v, $fail) use ($userUpdateRuels) {
            foreach ($v as $key => $value) {
                if (array_search($key, array_keys($userUpdateRuels)) === false) {
                    $fail('Invalid data key!');
                }
            }
        }];

        $array['userAccountAttributes'] = ['required_without:userAttributes', 'array', 'min:1', 'max:' . count($userAccountUpdateRuels), function ($a, $v, $fail) use ($userAccountUpdateRuels) {
            foreach ($v as $key => $value) {
                if (array_search($key, array_keys($userAccountUpdateRuels)) === false) {
                    $fail('Invalid data key!');
                }
            }
        }];

        foreach ($userUpdateRuels as $key => $value) {
            $array['userAttributes.' . $key] = $value;
        }

        foreach ($userAccountUpdateRuels as $key => $value) {
            $array['userAccountAttributes.' . $key] = $value;
        }

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
