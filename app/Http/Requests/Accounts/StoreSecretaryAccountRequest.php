<?php

namespace App\Http\Requests\Accounts;

use App\Rules\CheckEnryptedValuesIds;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;

class StoreSecretaryAccountRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $array = (include(base_path() . '/app/Rules/BuiltInRules/Models/User/rules.php'));

        $specialRules = (include(base_path() . '/app/Rules/BuiltInRules/Models/Secretary/rules.php'));

        $array = array_merge($array, $specialRules);

        $array['phonenumber_encrypted'] = ['required', 'string', new CheckEnryptedValuesIds];
        $array['phonenumber_verified_at_encrypted'] = ['required', 'string'];

        $array['username'][] = new ProhibitExtraFeilds($array);

        return $array;
    }
}
