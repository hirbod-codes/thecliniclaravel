<?php

namespace App\Http\Requests\Orders;

use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
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
            'username' =>           ['required_without:count', 'prohibits:count,lastOrderId', 'string'],
            'count' =>              ['required_without:username', 'string', 'prohibits:username'],
            'lastOrderId' =>        ['string', 'numeric', 'integer'],
            'priceOtherwiseTime' => ['boolean'],
            'operator' =>           ['string', 'prohibited_if:priceOtherwiseTime,null', 'required_with:priceOtherwiseTime', Rule::in(['<', '>', '=', '<>', '>=', '<='])],
            'price' =>              ['numeric', 'integer', 'prohibited_if:priceOtherwiseTime,null', 'prohibits:timeConsumption', 'required_if:priceOtherwiseTime,true'],
            'timeConsumption' =>    ['numeric', 'integer', 'prohibited_if:priceOtherwiseTime,null', 'prohibits:price', 'required_if:priceOtherwiseTime,false'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
