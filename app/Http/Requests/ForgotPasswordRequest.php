<?php

namespace App\Http\Requests;

use App\Rules\PresenceProhibitedWith;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
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

    public function initialRules(): array
    {
        return
            [
                'email' => array_merge((include(base_path() . '/app/Rules/BuiltInRules/Models/User/email.php'))['email_not_unique_not_required'], [
                    'exists:users,email',
                    'required_without:phonenumber',
                    new PresenceProhibitedWith(['phonenumber']),
                ]),
                'phonenumber' => array_merge((include(base_path() . '/app/Rules/BuiltInRules/Models/User/phonenumber.php'))['phonenumber_not_unique_not_required'], [
                    'exists:users,phonenumber',
                    'required_without:email',
                    new PresenceProhibitedWith(['email']),
                ])
            ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = $this->initialRules();

        $rules['email'][] = new ProhibitExtraFeilds($rules);

        return $rules;
    }
}
