<?php

namespace App\Http\Requests;

use App\Rules\PresenceProhibitedWith;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;

class LogInRequest extends FormRequest
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
        $rules = [
            'username' => array_merge((include(base_path() . '/app/Rules/BuiltInRules/Models/User/username.php'))['username_not_unique_not_required_exists'], [
                'required_without:email',
                new PresenceProhibitedWith(['email']),
            ]),
            'email' => array_merge((include(base_path() . '/app/Rules/BuiltInRules/Models/User/email.php'))['email_not_unique_not_required'], [
                'exists:users,email',
                'required_without:username',
                new PresenceProhibitedWith(['username']),
            ]),
            'password' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/password.php'))['password_string'],
            'remember' => ['boolean']
        ];

        array_unshift($rules[array_key_first($rules)], new ProhibitExtraFeilds($rules));

        return $rules;
    }
}
