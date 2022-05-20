<?php

namespace App\Http\Requests\Roles;

use App\Rules\ProhibitExtraFeilds;
use App\Rules\ValidatePrivilegeValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use TheClinicDataStructures\DataStructures\User\DSUser;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $privileges = DSUser::getPrivileges();

        $array = [
            'roleName' => (include(base_path() . '/app/Rules/BuiltInRules/Models/role.php'))['roleName'],
            'privilegeValues' => ['required', 'array', new ValidatePrivilegeValue],
        ];

        $array[array_key_first($array)][] = new ProhibitExtraFeilds($array);

        return $array;
    }
}
