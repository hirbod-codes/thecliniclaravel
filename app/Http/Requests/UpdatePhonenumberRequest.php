<?php

namespace App\Http\Requests;

use App\Auth\CheckAuthentication;
use App\Models\Privileges\UpdateUser;
use App\Rules\PresenceProhibitedWith;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePhonenumberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = (new CheckAuthentication)->getAuthenticated();

        $updateUsers = $user->authenticatableRole->role->role->updateUserSubjects;
        /** @var UpdateUser $updateUser */
        foreach ($updateUsers as $updateUser) {
            if ($updateUser->object !== null || $updateUser->relatedColumn->name !== 'phonenumber') {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'phonenumber' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/phonenumber.php'))['phonenumber_exists'],
            'newPhonenumber' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/phonenumber.php'))['phonenumber_not_unique'],
        ];

        array_unshift($rules[array_key_first($rules)], new ProhibitExtraFeilds($rules));

        return $rules;
    }
}
