<?php

namespace Database\Interactions\Orders\Creation;

use App\Models\BusinessDefault;
use App\Models\Order\RegularOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\DataStructures\Order\Regular\DSRegularOrder;
use App\UseCases\Orders\Interfaces\IDataBaseCreateRegularOrder;

class DatabaseCreateRegularOrder implements IDataBaseCreateRegularOrder
{
    public function createRegularOrder(
        DSUser $targetUser,
        int|null $price,
        int|null $timeConsumption
    ): DSRegularOrder {
        /** @var User $userModel */
        $userModel = User::query()->where('username', $targetUser->getUsername())->first();

        DB::beginTransaction();

        try {
            $order = $userModel->orders()->create();

            $regularOrder = new RegularOrder;
            $regularOrder->{$order->getForeignKey()} = $order->{$order->getKeyName()};
            $regularOrder->price = $price ?: BusinessDefault::firstOrFail()->default_regular_order_price;
            $regularOrder->needed_time = $timeConsumption ?: BusinessDefault::firstOrFail()->default_regular_order_time_consumption;
            if (!$regularOrder->save()) {
                throw new \RuntimeException('Failed to create the order', 500);
            }

            DB::commit();

            return $regularOrder->getDSRegularOrder();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
