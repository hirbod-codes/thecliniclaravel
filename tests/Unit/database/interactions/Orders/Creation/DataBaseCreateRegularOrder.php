<?php

namespace Database\Interactions\Orders\Creation;

use App\Models\Order\RegularOrder;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use App\PoliciesLogicDataStructures\DataStructures\Order\Regular\DSRegularOrder;

class DataBaseCreateRegularOrderTest extends TestCase
{
    use GetAuthenticatables;

    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testCreateRegularOrder()
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

            $price = $this->faker->numberBetween(10000000, 40000000);
            $timeConsumption = $this->faker->numberBetween(600, 5400);

            $dsOrder = (new DatabaseCreateRegularOrder)->createRegularOrder($dsAuthenticatable, $price, $timeConsumption);

            $authenticatable->fresh();
            $this->assertCount(1, $authenticatable->orders->all());
            $this->assertDatabaseHas($order->getTable(), [$authenticatable->getForeignKey() => $authenticatable->{$authenticatable->getKeyName()}]);

            $this->assertDatabaseHas((new RegularOrder)->getTable(), [
                'price' => $price,
                $order->getForeignKey() => $order->{$order->getKeyName()}
            ]);

            $this->assertInstanceOf(DSRegularOrder::class, $dsOrder);
            $this->assertEquals($timeConsumption, $dsOrder->getNeededTime());
        } finally {
            DB::rollBack();
        }
    }
}
