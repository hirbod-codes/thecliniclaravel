<?php

namespace App\Http\Requests\AccountDocuments;

use App\Auth\CheckAuthentication;
use App\Http\Requests\BaseFormRequest;
use App\Models\User;
use App\Rules\ProhibitExtraFeilds;

class GetAvatarRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = (new CheckAuthentication)->getAuthenticated();
        $input = $this->safe()->all();
        $targetUserRoleName = ($targetUser = User::query()->whereKey($input['accountId'])->firstOrFail())->authenticatableRole->role->roleName->name;
        $isSelf = $user->getKey() === $targetUser->getKey();

        $privilegesSubjects = $user->authenticatableRole->role->role->privilegesSubjects;
        foreach ($privilegesSubjects as $privilegesSubject) {
            if ($privilegesSubject->privilegeName->name !== 'editAvatar' || boolval($privilegesSubject->boolean_value) !== true) {
                continue;
            }

            if (($isSelf && $privilegesSubject->object !== null) || (!$isSelf && ($privilegesSubject->object === null || ($privilegesSubject !== null && $privilegesSubject->relatedObject->childRoleModel->roleName->name !== $targetUserRoleName)))) {
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
            'accountId' => ['required', 'integer', 'numeric', 'min:1'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
