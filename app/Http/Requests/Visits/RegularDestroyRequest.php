<?php

namespace App\Http\Requests\Visits;

use App\Auth\CheckAuthentication;
use App\Rules\ProhibitExtraFeilds;
use App\Http\Requests\BaseFormRequest;
use App\Models\Order\RegularOrder;
use App\Models\Privileges\deleteVisit;

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

        /** @var RegularOrder $order */
        $order = RegularOrder::query()->whereKey((int)$input['regularOrderId'])->firstOrFail();

        $targetUser = $order->order->user;
        $targetUserRoleName = $targetUser->authenticatableRole->role->roleName->name;
        $isSelf = $user->getKey() === $targetUser->getKey();

        /** @var deleteVisit $deleteVisit */
        foreach ($deleteVisits as $deleteVisit) {
            if ($deleteVisit->relatedBusiness->name !== 'regular') {
                continue;
            }

            if (($isSelf && $deleteVisit->object !== null) || (!$isSelf && (($deleteVisit->object === null || ($deleteVisit->object !== null && $deleteVisit->relatedObject->childRoleModel->roleName->name !== $targetUserRoleName))))) {
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
            'regularOrderId' => ['required', 'integer', 'numeric', 'min:1'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
