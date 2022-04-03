<?php

namespace Database\Interactions\Orders\Creation;

use App\Models\BusinessDefault;
use App\Models\Order\RegularOrder;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;

class DataBaseCreateDefaultRegularOrderTest extends TestCase
{
    use GetAuthenticatables;

    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testCreateDefaultRegularOrder()
    {
        DB::beginTransaction();

        try {
            $authenticatable = $this->getAuthenticatable('patient');
            $dsAuthenticatable = $authenticatable->getDataStructure();

            foreach ($authenticatable->user->orders as $order) {
                if (($regularOrder = $order->regularOrder) !== null) {
                    $found = true;
                    break;
                }
            }
            if (!isset($found)) {
                throw new \RuntimeException('Failure!!!', 500);
            }

            $dsOrder = (new DatabaseCreateDefaultRegularOrder)->createDefaultRegularOrder($dsAuthenticatable);

            $authenticatable->fresh();
            $this->assertCount(1, $authenticatable->user->orders->all());
            $this->assertDatabaseHas($order->getTable(), [$authenticatable->getForeignKey() => $authenticatable->{$authenticatable->getKeyName()}]);

            $this->assertDatabaseHas((new RegularOrder)->getTable(), [
                'price' => BusinessDefault::first()->default_regular_order_price,
                $order->getForeignKey() => $order->{$order->getKeyName()}
            ]);

            $this->assertInstanceOf(DSRegularOrder::class, $dsOrder);
            $this->assertEquals(BusinessDefault::first()->default_regular_order_time_consumption, $dsOrder->getNeededTime());
        } finally {
            DB::rollBack();
        }
    }
}
