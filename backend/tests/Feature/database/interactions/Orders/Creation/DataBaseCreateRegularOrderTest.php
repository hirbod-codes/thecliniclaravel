<?php

namespace Tests\Feature\database\interactions\Orders\Creation;

use App\Models\Order\RegularOrder;
use App\Models\User;
use Database\Interactions\Business\Interfaces\IDataBaseRetrieveBusinessSettings;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Database\Interactions\Orders\Creation\DatabaseCreateRegularOrder;
use Mockery;
use Mockery\MockInterface;

/**
 * @covers \Database\Interactions\Orders\Creation\DatabaseCreateRegularOrder
 */
class DataBaseCreateRegularOrderTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testCreateRegularOrder()
    {
        try {
            DB::beginTransaction();

            /** @var IDataBaseRetrieveBusinessSettings|MockInterface $iDataBaseRetrieveBusinessSettings */
            $iDataBaseRetrieveBusinessSettings = Mockery::mock(IDataBaseRetrieveBusinessSettings::class);

            $price = $this->faker->numberBetween(1000000, 8000000);
            $timeConsumption = $this->faker->numberBetween(600, 3600);

            /** @var User $user */
            $user = User::query()->firstOrFail();

            $regularOrder = (new DatabaseCreateRegularOrder($iDataBaseRetrieveBusinessSettings))->createRegularOrder($user, $price, $timeConsumption);
            /** @var Order $order */
            $order = $regularOrder->order;

            $this->assertInstanceOf(RegularOrder::class, $regularOrder);
            $this->assertDatabaseHas($order->getTable(), [$order->getKeyName() => $order->getKey(), $user->getForeignKey() => $user->getKey()]);
            $this->assertDatabaseHas($regularOrder->getTable(), [$regularOrder->getKeyName() => $regularOrder->getKey(), $order->getForeignKey() => $order->getKey(), 'price' => $price, 'needed_time' => $timeConsumption]);

            DB::rollBack();

            $this->assertDatabaseMissing($order->getTable(), [$order->getKeyName() => $order->getKey(), $user->getForeignKey() => $user->getKey()]);
            $this->assertDatabaseMissing($regularOrder->getTable(), [$regularOrder->getKeyName() => $regularOrder->getKey(), $order->getForeignKey() => $order->getKey()]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
