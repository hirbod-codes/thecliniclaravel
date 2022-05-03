<?php

namespace App\Http\Requests\Roles;

use App\Rules\ProhibitExtraFeilds;
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
            'accountId' => ['required', 'integer', 'numeric', 'min:1'],
            'privilege' => ['required', 'string', Rule::in($privileges)],
            'value' => ['required', 'string'],
        ];

        $array['value'][] = new ProhibitExtraFeilds($array);

        return $array;
    }
}
