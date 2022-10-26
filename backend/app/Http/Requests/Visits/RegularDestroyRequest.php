<?php

namespace App\Http\Requests\Visits;

use App\Auth\CheckAuthentication;
use App\Rules\ProhibitExtraFeilds;
use App\Http\Requests\BaseFormRequest;
use App\Models\Visit\RegularVisit;

class RegularDestroyRequest extends BaseFormRequest
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
        $deleteVisits = $user->authenticatableRole->role->role->deleteVisitSubjects;

        /** @var RegularVisit $regularVisit */
        $regularVisit = RegularVisit::query()->whereKey((int)$input['visitId'])->firstOrFail();
        $targetUser = $regularVisit->regularOrder->order->user;
        $isSelf = $user->getKey() === $targetUser->getKey();

        foreach ($deleteVisits as $deleteVisit) {
            if ($deleteVisit->relatedBusiness->name !== 'regular') {
                continue;
            }

            if (($isSelf && $deleteVisit->object !== null) || (!$isSelf && (($deleteVisit->object === null || ($deleteVisit->object !== null && $deleteVisit->relatedObject->getKey() !== $targetUser->authenticatableRole->role->role->getKey()))))) {
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
            'visitId' => ['required', 'integer', 'numeric', 'min:1'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
