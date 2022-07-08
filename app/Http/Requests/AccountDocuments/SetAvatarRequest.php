<?php

namespace App\Http\Requests\AccountDocuments;

use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;

class SetAvatarRequest extends FormRequest
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
        $array = [
            'accountId' => ['required', 'integer', 'numeric', 'min:1'],
            'avatar' => (include(base_path() . '/app/Rules/BuiltInRules/Models/avatar.php'))['avatar'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
