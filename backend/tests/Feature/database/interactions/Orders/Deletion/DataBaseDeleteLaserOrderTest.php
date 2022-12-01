<?php

namespace Tests\Feature\database\interactions\Orders\Deletion;

use App\Models\Auth\Patient;
use App\Models\Order\LaserOrder;
use App\Models\Order\LaserOrderPackage;
use App\Models\Order\LaserOrderPart;
use Database\Interactions\Orders\Deletion\DataBaseDeleteLaserOrder;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * @covers \Database\Interactions\Orders\Deletion\DataBaseDeleteLaserOrder
 */
class DataBaseDeleteLaserOrderTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function test()
    {
        try {
            DB::beginTransaction();

            $patient = Patient::query()->firstOrFail();
            $user = $patient->user;

            foreach ($user->orders as $order) {
                /** @var LaserOrder $laserOrder */
                if (($laserOrder = $order->laserOrder) !== null) {
                    $found = true;
                    break;
                }
            }
            if (!isset($found)) {
                throw new ModelNotFoundException();
            }

            $order = $laserOrder->order;

            $result = (new DataBaseDeleteLaserOrder)->deleteLaserOrder($laserOrder);

            $this->assertNull($result);
            $this->assertDatabaseMissing($laserOrder->getTable(), [$laserOrder->getKeyName() => $laserOrder->getKey()]);
            $this->assertDatabaseMissing($order->getTable(), [$order->getKeyName() => $order->getKey()]);
            $this->assertDatabaseMissing((new LaserOrderPart)->getTable(), [$laserOrder->getForeignKey() => $laserOrder->getKey()]);
            $this->assertDatabaseMissing((new LaserOrderPackage)->getTable(), [$laserOrder->getForeignKey() => $laserOrder->getKey()]);

            DB::rollBack();

            $this->assertDatabaseHas($laserOrder->getTable(), [$laserOrder->getKeyName() => $laserOrder->getKey()]);
            $this->assertDatabaseHas($order->getTable(), [$order->getKeyName() => $order->getKey()]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
