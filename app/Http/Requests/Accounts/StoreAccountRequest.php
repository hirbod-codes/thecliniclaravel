<?php

namespace App\Http\Requests\Accounts;

use App\Auth\CheckAuthentication;
use App\Http\Requests\BaseFormRequest;
use App\Models\RoleName;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Support\Facades\Request;

class StoreAccountRequest extends BaseFormRequest
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

        $createUser = $user->authenticatableRole->role->role->createUserSubjects;

        foreach ($createUser as $createUserModel) {
            if ($createUserModel->object !== null && $createUserModel->relatedObject->childRoleModel->roleName->name === class_basename(Request::path())) {
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
        $roleName = array_reverse(explode('/', $this->path()))[0];
        $userTypeModelFullname = RoleName::query()->where('name', '=', $roleName)->firstOrFail()->childRoleModel->getUserTypeModelFullname();

        $array = include(base_path() . '/app/Rules/BuiltInRules/Models/User/rules.php');

        $array = array_merge($array, include(base_path() . '/app/Rules/BuiltInRules/Models/' . class_basename($userTypeModelFullname) . '/updateRules.php'));

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
