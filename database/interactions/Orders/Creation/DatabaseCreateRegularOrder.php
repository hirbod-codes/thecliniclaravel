<?php

namespace Database\Interactions\Orders\Creation;

use App\Models\Order\RegularOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use TheClinicDataStructures\DataStructures\Order\Regular\DSRegularOrder;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Orders\Interfaces\IDataBaseCreateRegularOrder;

class DatabaseCreateRegularOrder implements IDataBaseCreateRegularOrder
{
    public function createRegularOrder(
        DSUser $targetUser,
        int $price,
        int $timeConsumption
    ): DSRegularOrder {
        /** @var User $userModel */
        $userModel = User::query()->where('username', $targetUser->getUsername())->first();

        DB::beginTransaction();

        try {
            $order = $userModel->orders()->create();

            $regularOrder = new RegularOrder;
            $regularOrder->{$order->getForeignKey()} = $order->{$order->getKeyName()};
            $regularOrder->price = $price;
            $regularOrder->needed_time = $timeConsumption;
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
