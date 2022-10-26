<?php

namespace Database\Interactions\Orders\Creation;

use App\Models\Order\RegularOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Database\Interactions\Business\DataBaseRetrieveBusinessSettings;
use Database\Interactions\Business\Interfaces\IDataBaseRetrieveBusinessSettings;
use Database\Interactions\Orders\Interfaces\IDataBaseCreateRegularOrder;

class DatabaseCreateRegularOrder implements IDataBaseCreateRegularOrder
{
    private IDataBaseRetrieveBusinessSettings $iDataBaseRetrieveBusinessSettings;

    public function __construct(null|IDataBaseRetrieveBusinessSettings $iDataBaseRetrieveBusinessSettings = null)
    {
        $this->iDataBaseRetrieveBusinessSettings = $iDataBaseRetrieveBusinessSettings ?: new DataBaseRetrieveBusinessSettings;
    }

    public function createRegularOrder(User $targetUser, int|null $price, int|null $timeConsumption): RegularOrder
    {
        try {
            DB::beginTransaction();

            $order = $targetUser->orders()->create();

            $regularOrder = new RegularOrder;

            $regularOrder->{$order->getForeignKey()} = $order->{$order->getKeyName()};
            $regularOrder->price = $price ?: $this->iDataBaseRetrieveBusinessSettings->getDefaultRegularOrderPrice();
            $regularOrder->needed_time = $timeConsumption ?: $this->iDataBaseRetrieveBusinessSettings->getDefaultRegularOrderTimeConsumption();

            $regularOrder->saveOrFail();

            DB::commit();

            return $regularOrder->refresh();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
