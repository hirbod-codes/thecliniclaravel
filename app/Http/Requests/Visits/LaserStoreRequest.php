<?php

namespace App\Http\Requests\Visits;

use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;
use TheClinicDataStructures\DataStructures\Time\DSWorkSchedule;

class LaserStoreRequest extends FormRequest
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
            'laserOrderId' => ['required', 'integer', 'numeric', 'min:1'],
            'targetUserId' => ['required', 'integer', 'numeric', 'min:1'],
            'weekDaysPeriods' => ['array', 'min:1', 'max:7', function (string $attribute, array $value, $fail) {
                foreach (array_keys($value) as $key) {
                    if (!in_array($key, DSWorkSchedule::$weekDays)) {
                        $fail(trans_choice('validation.in', 0, ['attribute' => $attribute]));
                    }
                }
            }],
            'weekDaysPeriods.*' => ['required', 'array', 'min:1'],
            'weekDaysPeriods.*.*' => ['required', 'array', 'size:2'],
            'weekDaysPeriods.*.*.*' => ['required', 'string', 'regex:/\A[0-9]{4}-[0-9]{2}-[0-9]{2} ([0-1]{1}[0-9]{1}|2[0-4]{1}):[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}\z/', function (string $attribute, string $value, $fail) {
                $key = explode('.', $attribute);
                if (!in_array(array_reverse($key)[0], ['start', 'end'])) {
                    $fail(trans_choice('validation.in', 0, ['attribute' => $attribute]));
                }
            }],
        ];

        $array['laserOrderId'] = new ProhibitExtraFeilds($array);

        return $array;
    }
}
