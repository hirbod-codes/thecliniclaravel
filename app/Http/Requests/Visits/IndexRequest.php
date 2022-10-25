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
            /** @var User $targetUser */
            $targetUser = User::query()->whereKey($input['accountId'])->firstOrFail();
            $isSelf = $user->getKey() === $targetUser->getKey();

            return $retrieveVisits->search(function (RetrieveVisit $v, $k) use ($targetUser, $isSelf) {
                return ($isSelf && $v->object === null) || (!$isSelf && $v->object !== null && $v->relatedObject->getKey() === $targetUser->authenticatableRole->role->role->getKey());
            }, true) !== false;
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
            /** @var User $targetUser */
            $targetUser = $order->order->user;
            $isSelf = $user->getKey() === $targetUser->getKey();

            return $retrieveVisits->search(function (RetrieveVisit $v, $k) use ($targetUser, $isSelf) {
                return ($isSelf && $v->object === null) || (!$isSelf && $v->object !== null && $v->relatedObject->getKey() === $targetUser->authenticatableRole->role->role->getKey());
            }, true) !== false;
        } else {
            $roleNameModel = RoleName::query()->where('name', '=', $input['roleName'])->firstOrFail();
            $isSelf = $roleNameModel->name === $user->authenticatableRole->role->roleName->name;
            $roleName = $roleNameModel->name;

            return $retrieveVisits->search(function (RetrieveVisit $v, $k) use ($roleName, $isSelf) {
                return ($isSelf && $v->object === null) || (!$isSelf && $v->object !== null && $v->relatedObject->childRoleModel->roleName->name === $roleName);
            }, true) !== false;
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
