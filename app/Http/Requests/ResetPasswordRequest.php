<?php

namespace App\Http\Requests;

use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
        $rules = (new ForgotPasswordRequest())->initialRules();

        $rules['code'] = ['required', 'integer', 'numeric'];
        $rules['password'] = (include(base_path() . '/app/Rules/BuiltInRules/Models/User/password.php'))['password'];
        $rules['password_confirmation'] = ['required', 'string', 'same:password'];

        $rules[array_key_first($rules)][] = new ProhibitExtraFeilds($rules);

        return $rules;
    }
}
