<?php

namespace Tests\Unit\database\interactions\Orders\Creation;

use App\Models\Order\Order;
use App\Models\Order\RegularOrder;
use App\Models\User;
use Database\Interactions\Business\Interfaces\IDataBaseRetrieveBusinessSettings;
use Database\Interactions\Orders\Creation\DatabaseCreateDefaultRegularOrder;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * @covers \Database\Interactions\Orders\Creation\DatabaseCreateDefaultRegularOrder
 */
class DataBaseCreateDefaultRegularOrderTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testCreateDefaultRegularOrder()
    {
        try {
            DB::beginTransaction();

            /** @var IDataBaseRetrieveBusinessSettings|MockInterface $iDataBaseRetrieveBusinessSettings */
            $iDataBaseRetrieveBusinessSettings = Mockery::mock(IDataBaseRetrieveBusinessSettings::class);
            $iDataBaseRetrieveBusinessSettings->shouldReceive('getDefaultRegularOrderPrice')->once()->andReturn(10);
            $iDataBaseRetrieveBusinessSettings->shouldReceive('getDefaultRegularOrderTimeConsumption')->once()->andReturn(10);

            /** @var User $user */
            $user = User::query()->firstOrFail();
            $regularOrder = (new DatabaseCreateDefaultRegularOrder($iDataBaseRetrieveBusinessSettings))->createDefaultRegularOrder($user);
            /** @var Order $order */
            $order = $regularOrder->order;

            $this->assertInstanceOf(RegularOrder::class, $regularOrder);
            $this->assertDatabaseHas($order->getTable(), [$order->getKeyName() => $order->getKey(), $user->getForeignKey() => $user->getKey()]);
            $this->assertDatabaseHas($regularOrder->getTable(), [$regularOrder->getKeyName() => $regularOrder->getKey(), $order->getForeignKey() => $order->getKey()]);

            DB::rollBack();

            $this->assertDatabaseMissing($order->getTable(), [$order->getKeyName() => $order->getKey(), $user->getForeignKey() => $user->getKey()]);
            $this->assertDatabaseMissing($regularOrder->getTable(), [$regularOrder->getKeyName() => $regularOrder->getKey(), $order->getForeignKey() => $order->getKey()]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
