<?php

namespace Database\Interactions\Orders\Creation;

use App\Models\Order\LaserOrder;
use App\Models\Order\LaserOrderPackage;
use App\Models\Order\LaserOrderPart;
use App\Models\Package\Package;
use App\Models\Part\Part;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\DataStructures\Order\DSPackage;
use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSPart;
use App\DataStructures\Order\DSParts;
use App\DataStructures\Order\Laser\DSLaserOrder;
use Database\Interactions\Orders\Interfaces\IDataBaseCreateLaserOrder;

class DatabaseCreateLaserOrder implements IDataBaseCreateLaserOrder
{
    public function createLaserOrder(User $targetUser, int $price, int $timeConsumption, int $priceWithoutDiscount, ?DSParts $parts = null, ?DSPackages $packages = null): LaserOrder
    {
        try {
            DB::beginTransaction();

            $order = $targetUser->orders()->create();

            $laserOrder = new LaserOrder;
            $laserOrder->{$order->getForeignKey()} = $order->{$order->getKeyName()};
            $laserOrder->price = $priceWithoutDiscount;
            $laserOrder->price_with_discount = $price;
            $laserOrder->needed_time = $timeConsumption;
            $laserOrder->saveOrFail();

            $laserOrderId = $laserOrder->{$laserOrder->getKeyName()};

            /** @var DSPart $part */
            foreach ($parts as $part) {
                $part = Part::query()->where('name', '=', $part->getName())->first();
                $partId = $part->{$part->getKeyName()};

                $laserOrderPart = new LaserOrderPart;
                $laserOrderPart->{$laserOrder->getForeignKey()} = $laserOrderId;
                $laserOrderPart->{$part->getForeignKey()} = $partId;
                $laserOrderPart->saveOrFail();
            }

            /** @var DSPackage $package */
            foreach ($packages as $package) {
                $package = Package::query()->where('name', '=', $package->getName())->first();
                $packageId = $package->{$package->getKeyName()};

                $laserOrderPackage = new LaserOrderPackage;
                $laserOrderPackage->{$laserOrder->getForeignKey()} = $laserOrderId;
                $laserOrderPackage->{$package->getForeignKey()} = $packageId;
                $laserOrderPackage->saveOrFail();
            }

            DB::commit();

            return $laserOrder->fresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
