<?php

namespace App\Http\Requests\Roles;

use App\Auth\CheckAuthentication;
use App\Rules\ProhibitExtraFeilds;
use App\Rules\ValidatePrivilegeValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\DataStructures\User\DSUser;
use App\Models\Privilege;
use App\Models\User;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $roleName = $this->safe()->all()['role'];

        return ($user = (new CheckAuthentication)->getAuthenticated()) === null
            ? false
            : ($user->authenticatableRole->role->role->privilegesSubjects->search(function (Privilege $v, $k) use ($roleName) {
                return $v->privilegeName->name === 'writeRoles' && $roleName === $v->relatedObject->childRoleModel->roleName->name;
            }, true) !== false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $array = [
            'customRoleName' => (include(base_path() . '/app/Rules/BuiltInRules/Models/role.php'))['roleName_unique'],
            'privilegeValue' => ['required', new ValidatePrivilegeValue],
            'role' => (include(base_path() . '/app/Rules/BuiltInRules/Models/role.php'))['role'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
