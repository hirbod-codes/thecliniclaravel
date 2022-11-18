<?php

namespace Database\Interactions\Orders\Creation;

use App\Models\Order\RegularOrder;
use App\Models\User;
use Database\Interactions\Business\DataBaseRetrieveBusinessSettings;
use Database\Interactions\Business\Interfaces\IDataBaseRetrieveBusinessSettings;
use Illuminate\Support\Facades\DB;
use Database\Interactions\Orders\Interfaces\IDataBaseCreateDefaultRegularOrder;

class DatabaseCreateDefaultRegularOrder implements IDataBaseCreateDefaultRegularOrder
{
    private IDataBaseRetrieveBusinessSettings $iDataBaseRetrieveBusinessSettings;

    public function __construct(null|IDataBaseRetrieveBusinessSettings $iDataBaseRetrieveBusinessSettings = null)
    {
        $this->iDataBaseRetrieveBusinessSettings = $iDataBaseRetrieveBusinessSettings ?: new DataBaseRetrieveBusinessSettings;
    }

    public function createDefaultRegularOrder(User $targetUser): RegularOrder
    {
        try {
            DB::beginTransaction();

            $order = $targetUser->orders()->create();

            $regularOrder = new RegularOrder;

            $regularOrder->{$order->getForeignKey()} = $order->getKey();
            $regularOrder->price = $this->iDataBaseRetrieveBusinessSettings->getDefaultRegularOrderPrice();
            $regularOrder->needed_time = $this->iDataBaseRetrieveBusinessSettings->getDefaultRegularOrderTimeConsumption();

            $regularOrder->saveOrFail();

            DB::commit();

            return $regularOrder->refresh();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
