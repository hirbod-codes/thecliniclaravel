<?php

namespace App\Http\Requests\Roles;

use App\Models\Privilege;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'accountId' => ['required', 'string', 'integer', 'numeric', 'min:0'],
            'privilege' => [
                'required', 'string', Rule::in($t = Privilege::query()->get('name')->map(function ($v, $k) {
                    return $v->name;
                }))
            ],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
