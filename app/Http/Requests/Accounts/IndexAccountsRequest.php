<?php

namespace App\Http\Requests\Accounts;

use Illuminate\Foundation\Http\FormRequest;

class IndexAccountsRequest extends FormRequest
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
        return [
            'roleName' => include (base_path() . '/app/Rules/BuiltInRules/Models/role.php')['role'],
            'lastAccountId' => ['nullable', 'integer', 'numeric'],
            'count' => ['required', 'integer', 'numeric']
        ];
    }
}
