<?php

namespace App\Http\Requests\Accounts;

use App\Rules\PhonenumberVerificationCode;
use App\Rules\ProhibitExtraFeilds;
use App\Rules\UniqueFullname;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use TheClinicDataStructures\DataStructures\User\DSUser;

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
    public function rulesWithoutCountLimit(): array
    {
        return [
            'code' => ['required', 'string', 'numeric', 'regex:/\A[0-9]{6}\z/', 'bail'],
            'role' => include (base_path() . '/app/Rules/BuiltInRules/Models/role.php')['role'],
            'phonenumber' => array_merge(include (base_path() . '/app/Rules/BuiltInRules/Models/User/phonenumber.php')['phonenumber'], 'bail', new PhonenumberVerificationCode),
            'firstname' => include (base_path() . '/app/Rules/BuiltInRules/Models/User/firstname.php')['firstname'],
            'lastname' => include (base_path() . '/app/Rules/BuiltInRules/Models/User/lastname.php')['lastname'],
            'username' => include (base_path() . '/app/Rules/BuiltInRules/Models/User/username.php')['username'],
            'email' => include (base_path() . '/app/Rules/BuiltInRules/Models/User/email.php')['email_optional'],
            'password' => include (base_path() . '/app/Rules/BuiltInRules/Models/User/password.php')['password'],
            'gender' => include (base_path() . '/app/Rules/BuiltInRules/Models/User/gender.php')['gender'],
            'avatar' => include (base_path() . '/app/Rules/BuiltInRules/Models/avatar.php')['avatar_optional'],
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $array = $this->rulesWithoutCountLimit();

        $array['code'][] = new ProhibitExtraFeilds($array);

        return $array;
    }
}
