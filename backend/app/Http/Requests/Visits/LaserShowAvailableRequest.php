<?php

namespace App\Http\Requests\Visits;

use App\Auth\CheckAuthentication;
use App\Rules\ProhibitExtraFeilds;
use App\DataStructures\Time\DSWeeklyTimePatterns;
use App\Http\Requests\BaseFormRequest;
use App\Http\Requests\TimeZoneConversionTrait;
use App\Models\Order\LaserOrder;
use Illuminate\Support\Facades\App;

class LaserShowAvailableRequest extends BaseFormRequest
{
    use TimeZoneConversionTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $input = $this->safe()->all();
        $user = (new CheckAuthentication)->getAuthenticated();
        $retrieveVisits = $user->authenticatableRole->role->role->retrieveVisitSubjects;

        /** @var LaserOrder $order */
        $order = LaserOrder::query()->whereKey((int)$input['laserOrderId'])->firstOrFail();

        $targetUser = $order->order->user;
        $isSelf = $user->getKey() === $targetUser->getKey();

        foreach ($retrieveVisits as $retrieveVisit) {
            if ($retrieveVisit->relatedBusiness->name !== 'laser') {
                continue;
            }

            if (($isSelf && $retrieveVisit->object !== null) || (!$isSelf && (($retrieveVisit->object === null || ($retrieveVisit->object !== null && $retrieveVisit->relatedObject->getKey() !== $targetUser->authenticatableRole->role->role->getKey()))))) {
                continue;
            }

            return true;
        }

        return false;
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
            'weeklyTimePatterns' => ['array', 'min:1', 'max:7', function ($attribute, $value, $fail) {
                foreach ($value as $k => $v) {
                    if (!in_array($k, DSWeeklyTimePatterns::$weekDays)) {
                        $fail(trans_choice('validation.in', 0, ['attribute' => trans_choice('validation.attributes.weeklyTimePatterns', 0)]));
                    }
                }
            }],
        ];

        foreach (DSWeeklyTimePatterns::$weekDays as $weekDay) {
            $array = array_merge($array, [
                'weeklyTimePatterns.' . $weekDay . '' => ['array', 'min:1'],
                'weeklyTimePatterns.' . $weekDay . '.*' => ['required_array_keys:start,end', 'array', 'size:2'],
                'weeklyTimePatterns.' . $weekDay . '.*.start' => ['string', 'date_format:H:i:s'],
                'weeklyTimePatterns.' . $weekDay . '.*.end' => ['string', 'date_format:H:i:s'],
            ]);
        }

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
            'weeklyTimePatterns' => [
                'array' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
                'min' => trans_choice('/Visits/visits.minimum-week-days', 0),
                'max' => trans_choice('/Visits/visits.maximum-week-days', 0),
            ],
            'weeklyTimePatterns.*' => [
                'requried_with' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
                'array' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
                'min' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
            ],
            'weeklyTimePatterns.*.*' => [
                'requried_with' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
                'array' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
                'min' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
                'size' => trans_choice('/Visits/visits.invalid-week-days-periods-format', 0),
            ],
            'weeklyTimePatterns.*.*.*' => [
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
            'weeklyTimePatterns' => trans_choice('validation.attributes.weeklyTimePatterns', 0),
            'weeklyTimePatterns.*' => trans_choice('validation.attributes.weeklyTimePatterns', 0),
            'weeklyTimePatterns.*.*' => trans_choice('validation.attributes.weeklyTimePatterns', 0),
            'weeklyTimePatterns.*.*.*' => trans_choice('validation.attributes.weeklyTimePatterns', 0),
        ];
    }
}
