<?php

namespace App\Http\Requests;

use App\Rules\PresenceProhibitedWithout;
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
                new PresenceProhibitedWithout(['email']),
            ]),
            'email' => array_merge((include(base_path() . '/app/Rules/BuiltInRules/Models/User/email.php'))['email_not_unique_not_required'], [
                'exists:users,email',
                'required_without:username',
                new PresenceProhibitedWithout(['username']),
            ]),
            'password' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/password.php'))['password_string'],
            'remember' => ['boolean']
        ];

        $rules['username'] = new ProhibitExtraFeilds($rules);

        return $rules;
    }
}
