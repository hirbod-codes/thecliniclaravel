<?php

namespace Tests\Unit\database\interactions\Orders\Creation;

use App\DataStructures\Order\DSPackage;
use App\DataStructures\Order\DSPart;
use App\Models\Order\LaserOrderPackage;
use App\Models\Order\LaserOrderPart;
use App\Models\Order\Order;
use App\Models\Package\Package;
use App\Models\Part\Part;
use App\Models\User;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Database\Interactions\Orders\Creation\DatabaseCreateLaserOrder;

/**
 * @covers \Database\Interactions\Orders\Creation\DatabaseCreateLaserOrder
 */
class DataBaseCreateLaserOrderTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testCreateLaserOrder(): void
    {
        try {
            DB::beginTransaction();

            /** @var User $user */
            $user = User::query()->firstOrFail();
            $gender = $user->gender;
            $userOrdersCount = count($user->orders);

            $priceWithoutDiscount = $this->faker->numberBetween(5000000, 40000000);
            $price = intval(0.8 * $priceWithoutDiscount);
            $timeConsumption = $this->faker->numberBetween(600, 3600 * 4);

            $parts = Part::query()->where('gender', '=', $gender)->get()->all();
            $dsParts = Part::getDSParts($this->faker->randomElements($parts, $this->faker->numberBetween(1, 5)), $gender);

            $packages = Package::query()->where('gender', '=', $gender)->get()->all();
            $dsPackages = Package::getDSPackages($this->faker->randomElements($packages, $this->faker->numberBetween(1, 2)), $gender);

            $laserOrder = (new DatabaseCreateLaserOrder)->createLaserOrder(
                $user,
                $price,
                $timeConsumption,
                $priceWithoutDiscount,
                $dsParts,
                $dsPackages
            );
            /** @var Order $order */
            $order = $laserOrder->order;

            $user->refresh();
            $this->assertCount($userOrdersCount + 1, $user->orders);
            $this->assertDatabaseHas($laserOrder->getTable(), [$order->getForeignKey() => $order->getKey(), 'price' => $priceWithoutDiscount, 'price_with_discount' => $price, 'needed_time' => $timeConsumption]);
            $this->assertDatabaseHas($order->getTable(), [$user->getForeignKey() => $user->getKey()]);

            /** @var DSPart $dsPart */
            foreach ($dsParts as $dsPart) {
                $part = Part::query()->where('name', '=', $dsPart->getName())->firstOrFail();

                $this->assertDatabaseHas((new LaserOrderPart)->getTable(), [$part->getForeignKey() => $part->getKey(), $laserOrder->getForeignKey() => $laserOrder->getKey()]);
            }

            /** @var DSPackage $dsPackage */
            foreach ($dsPackages as $dsPackage) {
                $package = Package::query()->where('name', '=', $dsPackage->getName())->firstOrFail();

                $this->assertDatabaseHas((new LaserOrderPackage)->getTable(), [$package->getForeignKey() => $package->getKey(), $laserOrder->getForeignKey() => $laserOrder->getKey()]);
            }

            DB::rollBack();

            $this->assertDatabaseMissing($laserOrder->getTable(), [$order->getForeignKey() => $order->getKey(), 'price' => $priceWithoutDiscount, 'price_with_discount' => $price, 'needed_time' => $timeConsumption]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
