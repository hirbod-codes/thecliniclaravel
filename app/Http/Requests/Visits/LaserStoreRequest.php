<?php

namespace App\Http\Requests\Visits;

use App\Auth\CheckAuthentication;
use App\Rules\ProhibitExtraFeilds;
use App\DataStructures\Time\DSWeekDaysPeriods;
use App\Http\Requests\BaseFormRequest;
use App\Models\Order\LaserOrder;
use App\Models\Privileges\CreateVisit;

class LaserStoreRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $input = $this->safe()->all();
        $user = (new CheckAuthentication)->getAuthenticated();
        $createVisits = $user->authenticatableRole->role->role->createVisitSubjects;

        $targetUserRoleName = ($targetUser = ($laserOrder = LaserOrder::query()->whereKey($input['laserOrderId'])->firstOrFail())->order->user)->authenticatableRole->role->roleName->name;
        $isSelf = $targetUser->getKey() === $user->getKey();

        /** @var CreateVisit $createVisit */
        foreach ($createVisits as $createVisit) {
            if ($createVisit->relatedBusiness->name !== 'laser') {
                continue;
            }

            if (($isSelf && $createVisit->object !== null) || (!$isSelf && (($createVisit->object === null || ($createVisit->object !== null && $createVisit->relatedObject->childRoleModel->roleName->name !== $targetUserRoleName))))) {
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
            'weekDaysPeriods' => ['array', 'min:1', 'max:7', function ($attribute, $value, $fail) {
                foreach ($value as $k => $v) {
                    if (!in_array($k, DSWeekDaysPeriods::$weekDays)) {
                        $fail(trans_choice('validation.in', 0, ['attribute' => trans_choice('validation.attributes.weekDaysPeriods', 0)]));
                    }
                }
            }],
        ];

        foreach (DSWeekDaysPeriods::$weekDays as $weekDay) {
            $array = array_merge($array, [
                'weekDaysPeriods.' . $weekDay . '' => ['array', 'min:1'],
                'weekDaysPeriods.' . $weekDay . '.*' => ['required_array_keys:start,end', 'array', 'size:2'],
                'weekDaysPeriods.' . $weekDay . '.*.start' => ['string', 'date_format:Y-m-d H:i:s'],
                'weekDaysPeriods.' . $weekDay . '.*.end' => ['string', 'date_format:Y-m-d H:i:s'],
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
