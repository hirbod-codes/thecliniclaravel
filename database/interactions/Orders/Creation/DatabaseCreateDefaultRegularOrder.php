<?php

namespace Database\Interactions\Orders\Creation;

use App\Models\BusinessDefault;
use App\Models\Order\RegularOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use TheClinicDataStructures\DataStructures\Order\Regular\DSRegularOrder;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Orders\Interfaces\IDataBaseCreateDefaultRegularOrder;

class DatabaseCreateDefaultRegularOrder implements IDataBaseCreateDefaultRegularOrder
{
    public function createDefaultRegularOrder(DSUser $targetUser): DSRegularOrder
    {
        $userModel = User::query()->where('username', $targetUser->getUsername())->first();

        DB::beginTransaction();

        try {
            $order = $userModel->order()->create();

            $regularOrder = new RegularOrder;
            $regularOrder->{$order->getForeignKey()} = $order->{$order->getKeyName()};
            $regularOrder->price = BusinessDefault::first()->default_regular_order_price;
            $regularOrder->needed_time = BusinessDefault::first()->default_regular_order_time_consumption;
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
