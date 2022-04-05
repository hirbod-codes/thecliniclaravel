<?php

namespace Database\Interactions\Orders\Creation;

use App\Models\Order\LaserOrder;
use App\Models\Order\LaserOrderPackage;
use App\Models\Order\LaserOrderPart;
use App\Models\Package\Package;
use App\Models\Part\Part;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use TheClinicDataStructures\DataStructures\Order\DSPackage;
use TheClinicDataStructures\DataStructures\Order\DSPackages;
use TheClinicDataStructures\DataStructures\Order\DSPart;
use TheClinicDataStructures\DataStructures\Order\DSParts;
use TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrder;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Orders\Interfaces\IDataBaseCreateLaserOrder;

class DatabaseCreateLaserOrder implements IDataBaseCreateLaserOrder
{
    public function createLaserOrder(
        DSUser $targetUser,
        int $price,
        int $timeConsumption,
        int $priceWithoutDiscount,
        ?DSParts $parts = null,
        ?DSPackages $packages = null
    ): DSLaserOrder {
        if (($userModel = User::query()->where('username', $targetUser->getUsername())->first()) === null) {
            throw new ModelNotFoundException('', 404);
        }

        DB::beginTransaction();

        try {
            $order = $userModel->order()->create();

            $laserOrder = new LaserOrder;
            $laserOrder->{$order->getForeignKey()} = $order->{$order->getKeyName()};
            $laserOrder->price = $priceWithoutDiscount;
            $laserOrder->price_with_discount = $price;
            $laserOrder->needed_time = $timeConsumption;
            if (!$laserOrder->save()) {
                throw new \RuntimeException('Failed to create the laser order', 500);
            }
            $laserOrderId = $laserOrder->{$laserOrder->getKeyName()};

            /** @var DSPart $part */
            foreach ($parts as $part) {
                $part = Part::query()->where('name', '=', $part->getName())->first();
                $partId = $part->{$part->getKeyName()};

                $laserOrderPart = new LaserOrderPart;
                $laserOrderPart->{$laserOrder->getForeignKey()} = $laserOrderId;
                $laserOrderPart->{$part->getForeignKey()} = $partId;
                if ($laserOrderPart->save()) {
                    throw new \RuntimeException('Failed to create the laser order', 500);
                }
            }

            /** @var DSPackage $package */
            foreach ($packages as $package) {
                $package = Package::query()->where('name', '=', $package->getName())->first();
                $packageId = $package->{$package->getKeyName()};

                $laserOrderPackage = new LaserOrderPackage;
                $laserOrderPackage->{$laserOrder->getForeignKey()} = $laserOrderId;
                $laserOrderPackage->{$package->getForeignKey()} = $packageId;
                if ($laserOrderPackage->save()) {
                    throw new \RuntimeException('Failed to create the laser order', 500);
                }
            }

            DB::commit();

            return $laserOrder->getDSLaserOrder();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
