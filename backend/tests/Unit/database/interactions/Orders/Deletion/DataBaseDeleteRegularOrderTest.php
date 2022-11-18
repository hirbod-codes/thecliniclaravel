<?php

namespace Tests\Unit\database\interactions\Orders\Deletion;

use App\Models\Auth\Patient;
use App\Models\Order\RegularOrder;
use Database\Interactions\Orders\Deletion\DataBaseDeleteRegularOrder;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * @covers \Database\Interactions\Orders\Deletion\DataBaseDeleteRegularOrder
 */
class DataBaseDeleteRegularOrderTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testDeleteRegularOrder(): void
    {
        try {
            DB::beginTransaction();

            $patient = Patient::query()->firstOrFail();
            $user = $patient->user;

            foreach ($user->orders as $order) {
                /** @var RegularOrder $regularOrder */
                if (($regularOrder = $order->regularOrder) !== null) {
                    $found = true;
                    break;
                }
            }
            if (!isset($found)) {
                throw new ModelNotFoundException();
            }

            $order = $regularOrder->order;

            $result = (new DataBaseDeleteRegularOrder)->deleteRegularOrder($regularOrder);

            $this->assertNull($result);
            $this->assertDatabaseMissing($regularOrder->getTable(), [$regularOrder->getKeyName() => $regularOrder->getKey()]);
            $this->assertDatabaseMissing($order->getTable(), [$order->getKeyName() => $order->getKey()]);

            DB::rollBack();

            $this->assertDatabaseHas($regularOrder->getTable(), [$regularOrder->getKeyName() => $regularOrder->getKey()]);
            $this->assertDatabaseHas($order->getTable(), [$order->getKeyName() => $order->getKey()]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
