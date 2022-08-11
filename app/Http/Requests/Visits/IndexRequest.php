<?php

namespace App\Http\Requests\Visits;

use App\Auth\CheckAuthentication;
use App\Http\Requests\BaseFormRequest;
use App\Models\Order\LaserOrder;
use App\Models\Order\RegularOrder;
use App\Models\Privileges\RetrieveVisit;
use App\Models\RoleName;
use App\Models\User;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Validation\Rule;

class IndexRequest extends BaseFormRequest
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
        $retrieveVisits = $user->authenticatableRole->role->role->retrieveVisitSubjects;

        if (isset($input['accountId'])) {
            $targetUser = User::query()->whereKey($input['accountId'])->firstOrFail();
            $targetUserRoleName = $targetUser->authenticatableRole->role->roleName->name;
            $isSelf = $user->getKey() === $targetUser->getKey();
        } elseif (isset($input['orderId'])) {
            switch ($input['businessName']) {
                case 'laser':
                    /** @var LaserOrder $order */
                    $order = LaserOrder::query()->whereKey((int)$input['orderId'])->firstOrFail();
                    break;

                case 'regular':
                    /** @var RegularOrder $order */
                    $order = RegularOrder::query()->whereKey((int)$input['orderId'])->firstOrFail();
                    break;

                default:
                    throw new \LogicException('!!!!', 500);
                    break;
            }
            $targetUser = $order->order->user;
            $targetUserRoleName = $targetUser->authenticatableRole->role->roleName->name;
            $isSelf = $user->getKey() === $targetUser->getKey();
        } else {
            $roleNameModel = RoleName::query()->where('name', '=', $input['roleName'])->firstOrFail();
            $isSelf = $roleNameModel->name === $user->authenticatableRole->role->roleName->name;
            $targetUserRoleName = $roleNameModel->name;
        }

        /** @var RetrieveVisit $retrieveVisit */
        foreach ($retrieveVisits as $retrieveVisit) {
            if ($retrieveVisit->relatedBusiness->name !== $input['businessName']) {
                continue;
            }

            if (($isSelf && $retrieveVisit->object !== null) || (!$isSelf && (($retrieveVisit->object === null || ($retrieveVisit->object !== null && $retrieveVisit->relatedObject->childRoleModel->roleName->name !== $targetUserRoleName))))) {
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
            'businessName' => (include(base_path() . '/app/Rules/BuiltInRules/business.php'))['businessNames'],
            'roleName' => array_merge((include(base_path() . '/app/Rules/BuiltInRules/Models/role.php'))['roleName_optional'], ['required_with:operator,timestamp', 'prohibits:accountId,orderId']),
            'accountId' => ['prohibits:count,lastVisitTimestamp,roleName,orderId,timestamp,operator', 'integer', 'numeric', 'min:1'],
            'orderId' => ['prohibits:count,lastVisitTimestamp,roleName,accountId,timestamp,operator', 'integer', 'numeric', 'min:1'],
            'timestamp' => ['required_with:operator', 'prohibits:orderId,accountId', 'integer', 'numeric', 'min:1'],
            'operator' => ['required_with:timestamp', 'prohibits:orderId,accountId', Rule::in(['>', '>=', '<', '<=', '=', '<>'])],
            'lastVisitTimestamp' => ['prohibits:orderId,accountId', 'integer', 'numeric', 'min:1'],
            'count' => ['required_with:operator,timestamp', 'prohibits:orderId,accountId'],
            'sortByTimestamp' => ['required', 'string', Rule::in(['desc', 'asc'])],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
