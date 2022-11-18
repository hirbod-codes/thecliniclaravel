<?php

namespace App\Http\Requests\Roles;

use App\Auth\CheckAuthentication;
use App\Models\Privilege;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;

class DestroyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $input = $this->safe()->all();
        return ($user = (new CheckAuthentication)->getAuthenticated()) === null
            ? false
            : ($user->authenticatableRole->role->role->privilegesSubjects->search(function (Privilege $v, $k) use ($input) {
                return $v->privilegeName->name === 'writeRoles' && $v->relatedObject->childRoleModel->roleName->name === $input['customRoleName'];
            }, true) !== false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $array = [];
        $array['customRoleName'] = (include(base_path() . '/app/Rules/BuiltInRules/Models/role.php'))['roleName'];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
