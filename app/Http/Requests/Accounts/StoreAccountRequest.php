<?php

namespace App\Http\Requests\Accounts;

use App\Rules\CheckEnryptedValuesIds;
use App\Rules\PhonenumberVerificationCode;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
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
     * @see RegisterUserRequest
     * @return array
     */
    public function initialRules(): array
    {
        return [
            'role' => (include(base_path() . '/app/Rules/BuiltInRules/Models/role.php'))['role'],
            'phonenumber' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/phonenumber.php'))['phonenumber'],
            'firstname' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/firstname.php'))['firstname'],
            'lastname' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/lastname.php'))['lastname'],
            'username' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/username.php'))['username'],
            'email' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/email.php'))['email_optional'],
            'password' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/password.php'))['password'],
            'password_confirmation' => ['required', 'string', 'same:password'],
            'gender' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/gender.php'))['gender'],
            'avatar' => (include(base_path() . '/app/Rules/BuiltInRules/Models/avatar.php'))['avatar_optional'],
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $array = $this->initialRules();

        $array['phonenumber_verified_at_encrypted'] = ['required', 'string', new CheckEnryptedValuesIds];
        $array['phonenumber_encrypted'] = ['required', 'string'];
        $array['firstname'][] = new ProhibitExtraFeilds($array);

        return $array;
    }
}
