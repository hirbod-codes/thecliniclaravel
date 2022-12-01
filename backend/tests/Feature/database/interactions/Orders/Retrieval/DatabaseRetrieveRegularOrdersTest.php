<?php

namespace Tests\Feature\database\interactions\Orders\Retrieval;

use App\Models\Auth\Patient;
use App\Models\Order\RegularOrder;
use Database\Interactions\Orders\Retrieval\DatabaseRetrieveRegularOrders;
use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;

/**
 * @covers \Database\Interactions\Orders\Retrieval\DatabaseRetrieveRegularOrders
 */
class DatabaseRetrieveRegularOrdersTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testGetRegularOrdersByPriceByUser(): void
    {
        $patient = Patient::query()->firstOrFail();
        $user = $patient->user;

        foreach ($user->orders as $order) {
            if (($regularOrder = $order->regularOrder) !== null) {
                $found = true;
                break;
            }
        }
        if (!isset($found)) {
            throw new \RuntimeException('Failure!!!', 500);
        }

        $operator = '=';
        $price = $regularOrder->price;

        $regularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByPriceByUser($operator, $price, $user);

        $this->assertIsArray($regularOrders);
        $this->assertContainsOnlyInstancesOf(RegularOrder::class, $regularOrders);
        $this->assertNotCount(0, $regularOrders);
        foreach ($regularOrders as $regularOrder) {
            $this->assertEquals($price, $regularOrder->price);
        }

        $operator = '>';
        $price = 1;

        $regularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByPriceByUser($operator, $price, $user);

        $this->assertIsArray($regularOrders);
        $this->assertContainsOnlyInstancesOf(RegularOrder::class, $regularOrders);
        $this->assertNotCount(0, $regularOrders);
        foreach ($regularOrders as $regularOrder) {
            $this->assertGreaterThan($price, $regularOrder->price);
        }
    }

    public function testGetRegularOrdersByPrice(): void
    {
        $patient = Patient::query()->firstOrFail();
        $user = $patient->user;

        foreach ($user->orders as $order) {
            if (($regularOrder = $order->regularOrder) !== null) {
                $found = true;
                break;
            }
        }
        if (!isset($found)) {
            throw new \RuntimeException('Failure!!!', 500);
        }

        $lastOrderId = null;
        $count = 10;
        $operator = '=';
        $price = $regularOrder->price;
        $roleName = 'patient';

        $regularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByPrice($roleName, $lastOrderId, $count, $operator, $price);

        $this->assertIsArray($regularOrders);
        $this->assertContainsOnlyInstancesOf(RegularOrder::class, $regularOrders);
        $this->assertLessThanOrEqual($count, count($regularOrders));
        foreach ($regularOrders as $regularOrder) {
            $this->assertEquals($price, $regularOrder->price);
        }

        $lastOrderId = null;
        $count = 10;
        $operator = '>';
        $price = 1;
        $roleName = 'patient';

        $regularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByPrice($roleName, $lastOrderId, $count, $operator, $price);

        $this->assertIsArray($regularOrders);
        $this->assertContainsOnlyInstancesOf(RegularOrder::class, $regularOrders);
        $this->assertCount($count, $regularOrders);
        foreach ($regularOrders as $regularOrder) {
            $this->assertGreaterThan($price, $regularOrder->price);
        }

        $lastOrderId = array_pop($regularOrders)->getKey();

        $regularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByPrice($roleName, $lastOrderId, $count, $operator, $price);

        $this->assertIsArray($regularOrders);
        $this->assertContainsOnlyInstancesOf(RegularOrder::class, $regularOrders);
        $this->assertCount($count, $regularOrders);
        $this->assertLessThan($lastOrderId, $regularOrders[0]->getKey());
        foreach ($regularOrders as $regularOrder) {
            $this->assertGreaterThan($price, $regularOrder->price);
        }
    }

    public function testGetRegularOrdersByTimeConsumptionByUser(): void
    {
        $patient = Patient::query()->firstOrFail();
        $user = $patient->user;

        foreach ($user->orders as $order) {
            if (($regularOrder = $order->regularOrder) !== null) {
                $found = true;
                break;
            }
        }
        if (!isset($found)) {
            throw new \RuntimeException('Failure!!!', 500);
        }

        $operator = '=';
        $timeConsumption = $regularOrder->needed_time;

        $regularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByTimeConsumptionByUser($operator, $timeConsumption, $user);

        $this->assertIsArray($regularOrders);
        $this->assertContainsOnlyInstancesOf(RegularOrder::class, $regularOrders);
        $this->assertGreaterThanOrEqual(1, count($regularOrders));
        foreach ($regularOrders as $regularOrder) {
            $this->assertEquals($timeConsumption, $regularOrder->needed_time);
        }

        $operator = '>';
        $timeConsumption = 1;

        $regularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByTimeConsumptionByUser($operator, $timeConsumption, $user);

        $this->assertIsArray($regularOrders);
        $this->assertContainsOnlyInstancesOf(RegularOrder::class, $regularOrders);
        foreach ($regularOrders as $regularOrder) {
            $this->assertGreaterThan($timeConsumption, $regularOrder->needed_time);
        }
    }

    public function testGetRegularOrdersByTimeConsumption(): void
    {
        $patient = Patient::query()->firstOrFail();
        $user = $patient->user;

        foreach ($user->orders as $order) {
            if (($regularOrder = $order->regularOrder) !== null) {
                $found = true;
                break;
            }
        }
        if (!isset($found)) {
            throw new \RuntimeException('Failure!!!', 500);
        }

        $roleName = 'patient';

        $lastOrderId = null;
        $count = 10;
        $operator = '=';
        $timeConsumption = $regularOrder->needed_time;

        $regularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByTimeConsumption($roleName, $count, $operator, $timeConsumption, $lastOrderId);

        $this->assertIsArray($regularOrders);
        $this->assertContainsOnlyInstancesOf(RegularOrder::class, $regularOrders);
        $this->assertLessThanOrEqual($count, count($regularOrders));
        foreach ($regularOrders as $regularOrder) {
            $this->assertEquals($timeConsumption, $regularOrder->needed_time);
        }

        $lastOrderId = null;
        $count = 10;
        $operator = '>';
        $timeConsumption = 1;

        $regularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByTimeConsumption($roleName, $count, $operator, $timeConsumption, $lastOrderId);

        $this->assertIsArray($regularOrders);
        $this->assertContainsOnlyInstancesOf(RegularOrder::class, $regularOrders);
        $this->assertCount($count, $regularOrders);
        foreach ($regularOrders as $regularOrder) {
            $this->assertGreaterThan($timeConsumption, $regularOrder->needed_time);
        }

        $lastOrderId = array_pop($regularOrders)->getKey();

        $regularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByTimeConsumption($roleName, $count, $operator, $timeConsumption, $lastOrderId);

        $this->assertIsArray($regularOrders);
        $this->assertContainsOnlyInstancesOf(RegularOrder::class, $regularOrders);
        $this->assertCount($count, $regularOrders);
        $this->assertLessThan($lastOrderId, $regularOrders[0]->getKey());
        foreach ($regularOrders as $regularOrder) {
            $this->assertGreaterThan($timeConsumption, $regularOrder->needed_time);
        }
    }

    public function testGetRegularOrdersByUser(): void
    {
        $patient = Patient::query()->firstOrFail();
        $user = $patient->user;

        $regularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByUser($user);

        $this->assertIsArray($regularOrders);
        $this->assertNotCount(0, $regularOrders);
        $this->assertContainsOnlyInstancesOf(RegularOrder::class, $regularOrders);
        /** @var RegularOrder $regularOrder */
        foreach ($regularOrders as $regularOrder) {
            $this->assertEquals($user->getKey(), $regularOrder->order->user->getKey());
        }
    }

    public function testGetRegularOrderById(): void
    {
        $regularOrder = RegularOrder::query()->firstOrFail();

        $foundedRegularOrder = (new DatabaseRetrieveRegularOrders)->getRegularOrderById($regularOrder->getKey());

        $this->assertInstanceOf(RegularOrder::class, $foundedRegularOrder);
        $this->assertEquals($regularOrder->getKey(), $foundedRegularOrder->getKey());
    }

    public function testGetRegularOrders(): void
    {
        $lastOrderId = null;
        $count = 10;
        $roleName = 'patient';

        $regularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrders($roleName, $count, $lastOrderId);

        $this->assertIsArray($regularOrders);
        $this->assertContainsOnlyInstancesOf(RegularOrder::class, $regularOrders);
        $this->assertCount($count, $regularOrders);

        foreach ($regularOrders as $regularOrder) {
            $t = $regularOrder->order->user->authenticatableRole->role->roleName->name;
            $this->assertEquals($roleName, $t);
        }

        $lastOrderId = array_pop($regularOrders)->getKey();

        $regularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrders($roleName, $count, $lastOrderId);

        $this->assertIsArray($regularOrders);
        $this->assertContainsOnlyInstancesOf(RegularOrder::class, $regularOrders);
        $this->assertCount($count, $regularOrders);
        $this->assertLessThan($lastOrderId, $regularOrders[0]->getKey());

        foreach ($regularOrders as $regularOrder) {
            $t = $regularOrder->order->user->authenticatableRole->role->roleName->name;
            $this->assertEquals($roleName, $t);
        }
    }
}
