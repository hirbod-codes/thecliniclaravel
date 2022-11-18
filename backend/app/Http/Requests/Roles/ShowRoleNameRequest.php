<?php

namespace App\Http\Requests\Roles;

use App\Auth\CheckAuthentication;
use App\Http\Requests\BaseFormRequest;
use App\Models\User;
use App\Rules\ProhibitExtraFeilds;

class ShowRoleNameRequest extends BaseFormRequest
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

        /** @var User $targetUser */
        $targetUser = User::query()->whereKey($input['accountId'])->firstOrFail();

        $isSelf = $targetUser->getKey() === $user->getKey();

        foreach ($user->authenticatableRole->role->role->retrieveUserSubjects as $retrieveUser) {
            if (($isSelf && $retrieveUser->object !== null) || (!$isSelf && ($retrieveUser->object === null || ($retrieveUser->object !== null && $retrieveUser->relatedObject->getKey() !== $targetUser->authenticatableRole->role->role->getKey())))) {
                continue;
            }
            return true;
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
        $array = [
            'accountId' => ['required', 'string', 'integer', 'numeric', 'min:1'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
