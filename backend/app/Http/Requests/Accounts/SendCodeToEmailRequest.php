<?php

namespace App\Http\Requests\Accounts;

use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;

class SendCodeToEmailRequest extends FormRequest
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
            'email' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/email.php'))['email_exists'],
        ];
        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));
        return $array;
    }
}
