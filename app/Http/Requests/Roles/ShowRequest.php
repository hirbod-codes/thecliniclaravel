<?php

namespace App\Http\Requests\Roles;

use App\Auth\CheckAuthentication;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;
use TheClinicDataStructures\DataStructures\User\DSAdmin;

class ShowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return (new CheckAuthentication)->getAuthenticatedDSUser() instanceof DSAdmin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $array = [
            'accountId' => ['required', 'string', 'integer', 'numeric', 'min:0'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
