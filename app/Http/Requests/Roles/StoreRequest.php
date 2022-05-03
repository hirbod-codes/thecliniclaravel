<?php

namespace App\Http\Requests\Roles;

use App\Rules\ProhibitExtraFeilds;
use App\Rules\ValidatePrivilegeValue;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
        $array= [
            'customRoleName' => ['required', 'string', 'regex:/\A[a-zA-Z0-9\/_-]+\z/'],
            'privilegeValue' => ['required', new ValidatePrivilegeValue],
        ];

        $array['customRoleName'][] = new ProhibitExtraFeilds($array);

        return $array;
    }
}
