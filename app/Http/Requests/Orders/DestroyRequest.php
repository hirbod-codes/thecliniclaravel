<?php

namespace App\Http\Requests\Orders;

use App\Auth\CheckAuthentication;
use App\Http\Requests\BaseFormRequest;
use App\Models\Order\LaserOrder;
use App\Models\Order\RegularOrder;
use App\Models\Privileges\DeleteOrder;
use App\Models\User;
use App\Rules\ProhibitExtraFeilds;
use Illuminate\Support\Facades\Request;

class DestroyRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = (new CheckAuthentication)->getAuthenticated();
        $input = $this->safe()->all();

        switch ($input['businessName']) {
            case 'laser':
                $childOrder = LaserOrder::query()->whereKey($input['childOrderId'])->firstOrFail();
                break;

            case 'regular':
                $childOrder = RegularOrder::query()->whereKey($input['childOrderId'])->firstOrFail();
                break;

            default:
                throw new \LogicException('!!!', 500);
                break;
        }

        $targetUserRoleName = ($targetUser = $childOrder->order->user)->authenticatableRole->role->roleName->name;
        $deleteOrders = $targetUser->authenticatableRole->role->role->deleteOrderSubjects;
        $isSelf = $targetUser->getKey() === $user->getKey();

        /** @var DeleteOrder $deleteOrder */
        foreach ($deleteOrders as $deleteOrder) {
            if ($deleteOrder->relatedBusiness->name !== $input['businessName']) {
                continue;
            }

            if (($isSelf && $deleteOrder->object !== null) || (!$isSelf && ($deleteOrder->object === null || ($deleteOrder->object !== null && $deleteOrder->relatedObject->roleName->name !== $targetUserRoleName))) ) {
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
            'childOrderId' =>       ['string', 'numeric', 'integer'],
            'businessName' => (include(base_path() . '/app/Rules/BuiltInRules/business.php'))['businessNames'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }
}
