<?php

namespace App\Http\Requests\Orders;

use App\Auth\CheckAuthentication;
use App\Http\Requests\BaseFormRequest;
use App\Models\Order\LaserOrder;
use App\Models\Order\RegularOrder;
use App\Models\User;
use App\Rules\ProhibitExtraFeilds;

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

        /** @var User $targetUser */
        $targetUser = $childOrder->order->user;

        $isSelf = $targetUser->getKey() === $user->getKey();

        $deleteOrders = $user->authenticatableRole->role->role->deleteOrderSubjects;
        foreach ($deleteOrders as $deleteOrder) {
            if ($deleteOrder->relatedBusiness->name !== $input['businessName']) {
                continue;
            }

            if (($isSelf && $deleteOrder->object !== null) || (!$isSelf && ($deleteOrder->object === null || ($deleteOrder->object !== null && $deleteOrder->relatedObject->getKey() !== $targetUser->authenticatableRole->role->role->getKey())))) {
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
            'childOrderId' => ['string', 'numeric', 'integer'],
            'businessName' => (include(base_path() . '/app/Rules/BuiltInRules/business.php'))['businessNames'],
        ];

        array_unshift($array[array_key_first($array)], new ProhibitExtraFeilds($array));

        return $array;
    }

    protected function prepareForValidation()
    {
        $pathParams = array_reverse(explode('/', $this->path()));

        $this->replace(array_merge($this->all(), ['childOrderId' => $pathParams[0], 'businessName' => $pathParams[1]]));
    }
}
