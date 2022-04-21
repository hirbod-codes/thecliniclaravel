<?php

namespace App\Http\Requests;

use App\Http\Requests\Accounts\StoreAccountRequest;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        $array = (new StoreAccountRequest())->rulesWithoutCountLimit();

        unset($array['role']);

        $array['code'][] = new ProhibitExtraFeilds($array);

        return $array;
    }
}
