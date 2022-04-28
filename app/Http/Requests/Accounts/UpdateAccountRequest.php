<?php

namespace App\Http\Requests\Accounts;

use App\Rules\ProhibitExtraFeilds;
use App\Rules\UniqueFullname;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
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
        $array = [
            'phonenumber' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/phonenumber.php'))['phonenumber_optional'],
            'firstname' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/firstname.php'))['firstname_optional'],
            'lastname' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/lastname.php'))['lastname_optional'],
            'username' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/username.php'))['username_optional'],
            'email' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/email.php'))['email_optional'],
            'gender' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/gender.php'))['gender_optional'],
            'avatar' => (include(base_path() . '/app/Rules/BuiltInRules/Models/avatar.php'))['avatar_optional'],
        ];

        $array['username'][] = new ProhibitExtraFeilds($array);

        return $array;
    }
}
