<?php

namespace App\Http\Requests\Visits;

use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegularIndexRequest extends FormRequest
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
            'sortByTimestamp' => ['required', 'string', Rule::in(['desc', 'asc'])],
            'regularOrderId' => ['prohibits:timestamp,operator', 'integer', 'numeric', 'min:1'],
            'timestamp' => ['required_with:operator', 'prohibits:accountId', 'integer', 'numeric', 'min:1'],
            'operator' => ['required_with:timestamp', 'prohibits:accountId', Rule::in(['>', '>=', '<', '<=', '=', '<>'])],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
