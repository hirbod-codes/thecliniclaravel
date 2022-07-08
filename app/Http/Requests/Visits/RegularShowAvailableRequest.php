<?php

namespace App\Http\Requests\Visits;

use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;
use TheClinicDataStructures\DataStructures\Time\DSWeekDaysPeriods;

class RegularShowAvailableRequest extends FormRequest
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
            'weekDaysPeriods' => ['array', 'min:1', 'max:7', function (string $attribute, array $value, $fail) {
                foreach (array_keys($value) as $key) {
                    if (!in_array($key, DSWeekDaysPeriods::$weekDays)) {
                        $fail(trans_choice('validation.in', 0, ['attribute' => trans_choice('validation.attributes.weekDaysPeriods', 0)]));
                    }
                }
            }],
            'weekDaysPeriods.*' => ['required_with:weekDaysPeriods', 'array', 'min:1'],
            'weekDaysPeriods.*.*' => ['required_with:weekDaysPeriods', 'array', 'size:2'],
            'weekDaysPeriods.*.*.*' => ['required_with:weekDaysPeriods', 'string', 'regex:/\A[0-9]{4}-[0-9]{1,2}-[0-9]{1,2} ([0-1]{1}[0-9]{1}|2[0-4]{1}):[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}\z/', function (string $attribute, string $value, $fail) {
                $key = explode('.', $attribute);
                if (!in_array(array_reverse($key)[0], ['start', 'end'])) {
                    $fail(trans_choice('validation.in', 0, ['attribute' => trans_choice('validation.attributes.weekDaysPeriods', 0)]));
                }
            }],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'weekDaysPeriods' => [
                'array' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
                'min' => trans_choice('/Visits/visits.minimum-week-days', 0),
                'max' => trans_choice('/Visits/visits.maximum-week-days', 0),
            ],
            'weekDaysPeriods.*' => [
                'requried_with' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
                'array' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
                'min' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
            ],
            'weekDaysPeriods.*.*' => [
                'requried_with' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
                'array' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
                'min' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
                'size' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
            ],
            'weekDaysPeriods.*.*.*' => [
                'requried_with' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
                'string' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
                'regex' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'weekDaysPeriods' => trans_choice('validation.attributes.weekDaysPeriods', 0),
            'weekDaysPeriods.*' => trans_choice('validation.attributes.weekDaysPeriods', 0),
            'weekDaysPeriods.*.*' => trans_choice('validation.attributes.weekDaysPeriods', 0),
            'weekDaysPeriods.*.*.*' => trans_choice('validation.attributes.weekDaysPeriods', 0),
        ];
    }
}
