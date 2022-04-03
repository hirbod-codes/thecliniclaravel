<?php

namespace Database\Interactions\Orders\Creation;

use App\Models\Order\LaserOrder;
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
use Tests\Unit\Traits\GetAuthenticatables;
use TheClinicDataStructures\DataStructures\Order\DSPackage;
use TheClinicDataStructures\DataStructures\Order\DSPackages;
use TheClinicDataStructures\DataStructures\Order\DSPart;
use TheClinicDataStructures\DataStructures\Order\DSParts;
use TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrder;

class DataBaseCreateLaserOrderTest extends TestCase
{
    use GetAuthenticatables;

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

            $authenticatable = $this->getAuthenticatable('patient');
            $dsAuthenticatable = $authenticatable->getDataStructure();

            foreach ($authenticatable->user->orders as $order) {
                if (($laserOrder = $order->laserOrder()->first()) !== null) {
                    $found = true;
                    break;
                }
            }
            if (!isset($found)) {
                throw new \RuntimeException('Failure!!!', 500);
            }

            $authenticatable->orders()->delete();

            $priceWithoutDiscount = $this->faker->numberBetween(5000000, 40000000);
            $price = 0.8 * $priceWithoutDiscount;
            $timeConsumption = $this->faker->numberBetween(600, 3600 * 4);

            $gender = $this->faker->randomElement(['Male', 'Female']);

            $parts = Part::query()->where('gender', '=', $gender)->get()->all();
            $dsParts = Part::getDSParts($this->faker->randomElements($parts, $this->faker->numberBetween(1, 5)), $gender);

            $packages = Package::query()->where('gender', '=', $gender)->get()->all();
            $dsPackages = Package::getDSParts($this->faker->randomElements($packages, $this->faker->numberBetween(1, 2)), $gender);

            $dsOrder = (new DatabaseCreateLaserOrder)->createLaserOrder(
                $dsAuthenticatable,
                $price,
                $timeConsumption,
                $priceWithoutDiscount,
                $dsParts,
                $dsPackages
            );

            $authenticatable->fresh();
            $this->assertCount(1, $authenticatable->orders);
            $this->assertDatabaseHas($order->getTable(), [$authenticatable->getForeignKey() => $authenticatable->{$authenticatable->getKeyName()}]);

            $this->assertDatabaseHas((new LaserOrder)->getTable(), [
                'price' => $priceWithoutDiscount,
                $order->getForeignKey() => $order->{$order->getKeyName()}
            ]);

            $this->assertInstanceOf(DSLaserOrder::class, $dsOrder);
            $this->assertEquals($priceWithoutDiscount, $dsOrder->getPrice());
            $this->assertEquals($price, $dsOrder->getPriceWithDiscount());
            $this->assertEquals($timeConsumption, $dsOrder->getNeededTime());

            $this->assertInstanceOf(DSParts::class, $dsOrder->getParts());
            $this->assertCount(count($dsParts), $dsOrder->getParts());
            /** @var DSPart $dsOrderDSPart */
            foreach ($dsOrder->getParts() as $dsOrderDSPart) {
                $found = false;
                /** @var DSPart $dsPart */
                foreach ($dsParts as $dsPart) {
                    if ($dsPart->getName() === $dsOrderDSPart->getName()) {
                        $found = true;
                    }
                }

                if (!$found) {
                    throw new \RuntimeException('Failure!!!', 500);
                }

                $this->assertDatabaseHas((new LaserOrderPart)->getTable(), [
                    (new LaserOrder)->getForeignKey() => ($laserOrder = $order->laserOrder)->{$laserOrder->getKeyName()},
                    (new Part)->getForeignKey() => $dsOrderDSPart->getId(),
                ]);
            }

            $this->assertInstanceOf(DSPackages::class, $dsOrder->getPackages());
            $this->assertCount(count($dsPackages), $dsOrder->getPackages());
            /** @var DSPackage $dsOrderDSPackage */
            foreach ($dsOrder->getPackages() as $dsOrderDSPackage) {
                $found = false;
                /** @var DSPackage $dsPackage */
                foreach ($dsPackages as $dsPackage) {
                    if ($dsPackage->getName() === $dsOrderDSPackage->getName()) {
                        $found = true;
                    }
                }

                if (!$found) {
                    throw new \RuntimeException('Failure!!!', 500);
                }

                $this->assertDatabaseHas((new LaserOrderPackage)->getTable(), [
                    (new LaserOrder)->getForeignKey() => ($laserOrder = $order->laserOrder)->{$laserOrder->getKeyName()},
                    (new Package)->getForeignKey() => $dsOrderDSPackage->getId(),
                ]);
            }
        } finally {
            DB::rollBack();
        }
    }
}
