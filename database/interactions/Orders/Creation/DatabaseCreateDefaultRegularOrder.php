<?php

namespace Database\Interactions\Orders\Creation;

use App\Models\BusinessDefault;
use App\Models\Order\RegularOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\DataStructures\Order\Regular\DSRegularOrder;
use Database\Interactions\Orders\Interfaces\IDataBaseCreateDefaultRegularOrder;

class DatabaseCreateDefaultRegularOrder implements IDataBaseCreateDefaultRegularOrder
{
    public function createDefaultRegularOrder(User $targetUser): RegularOrder
    {
        DB::beginTransaction();

        try {
            $order = $targetUser->orders()->create();

            $regularOrder = new RegularOrder;
            $regularOrder->{$order->getForeignKey()} = $order->getKey();
            $regularOrder->price = BusinessDefault::firstOrFail()->default_regular_order_price;
            $regularOrder->needed_time = BusinessDefault::firstOrFail()->default_regular_order_time_consumption;
            if (!$regularOrder->save()) {
                throw new \RuntimeException('Failed to create the order', 500);
            }

            DB::commit();

            return $regularOrder->fresh();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
