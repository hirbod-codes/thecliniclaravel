<?php

namespace App\Http\Requests\BusinessDefault;

use App\Auth\CheckAuthentication;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Foundation\Http\FormRequest;
use App\DataStructures\Time\DSWorkSchedule;
use App\DataStructures\User\DSAdmin;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return (new CheckAuthentication)->getAuthenticatedDSUser() instanceof DSAdmin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $array = [
            'min_age' => ['integer', 'numeric', 'min:1'],
            'visit_alert_deley' => ['integer', 'numeric', 'min:1'],
            'default_regular_order_price' => ['integer', 'numeric', 'min:1'],
            'default_regular_order_time_consumption' => ['integer', 'numeric', 'min:1'],
            'work_schedule' => ['array', 'min:1', 'max:7', function (string $attribute, array $value, $fail) {
                foreach (array_keys($value) as $key) {
                    if (!in_array($key, DSWorkSchedule::$weekDays)) {
                        $fail(trans_choice('validation.in', 0, ['attribute' => $attribute]));
                    }
                }
            }],
            'work_schedule.*' => ['required', 'array', 'min:1'],
            'work_schedule.*.*' => ['required', 'array', 'size:2'],
            'work_schedule.*.*.*' => ['required', 'string', 'regex:/\A[0-9]{4}-[0-9]{2}-[0-9]{2} ([0-1]{1}[0-9]{1}|2[0-4]{1}):[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}\z/', function (string $attribute, string $value, $fail) {
                $key = explode('.', $attribute);
                if (!in_array(array_reverse($key)[0], ['start', 'end'])) {
                    $fail(trans_choice('validation.in', 0, ['attribute' => $attribute]));
                }
            }],
            'down_times' => ['array', 'min:1'],
            'down_times.*' => ['required', 'array', 'size:3'],
            'down_times.*.start' => ['required', 'string', 'regex:/\A[0-9]{4}-[0-9]{2}-[0-9]{2} ([0-1]{1}[0-9]{1}|2[0-4]{1}):[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}\z/'],
            'down_times.*.end' => ['required', 'string', 'regex:/\A[0-9]{4}-[0-9]{2}-[0-9]{2} ([0-1]{1}[0-9]{1}|2[0-4]{1}):[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}\z/'],
            'down_times.*.name' => ['required', 'string', 'min:1'],
            'genders' => ['string', 'min:1'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
