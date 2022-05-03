<?php

namespace App\Http\Requests\Roles;

use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
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
            'self' => ['required_without:accountId', 'boolean'],
            'accountId' => ['required_without:self', 'string', 'integer', 'numeric', 'min:1'],
        ];

        $array['self'][] = new ProhibitExtraFeilds($array);

        return $array;
    }
}
