<?php

namespace App\Http\Requests\Visits;

use App\Auth\CheckAuthentication;
use App\Rules\ProhibitExtraFeilds;
use App\Http\Requests\BaseFormRequest;
use App\Models\Visit\LaserVisit;

class LaserDestroyRequest extends BaseFormRequest
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

        /** @var LaserVisit $laserVisit */
        $laserVisit = LaserVisit::query()->whereKey((int)$input['visitId'])->firstOrFail();
        $targetUser = $laserVisit->laserOrder->order->user;
        $isSelf = $user->getKey() === $targetUser->getKey();

        foreach ($deleteVisits as $deleteVisit) {
            if ($deleteVisit->relatedBusiness->name !== 'laser') {
                continue;
            }

            if (($isSelf && $deleteVisit->object !== null) || (!$isSelf && (($deleteVisit->object === null || ($deleteVisit->object !== null && $deleteVisit->relatedObject->getKey() !== $targetUser))))) {
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

    protected function prepareForValidation()
    {
        $this->replace(array_merge($this->all(), ['visitId' => class_basename($this->path())]));
    }
}
