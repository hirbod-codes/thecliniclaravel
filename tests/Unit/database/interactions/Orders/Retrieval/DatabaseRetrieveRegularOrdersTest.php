<?php

namespace Tests\Unit\database\interactions\Orders\Retrieval;

use Database\Interactions\Orders\Retrieval\DatabaseRetrieveRegularOrders;
use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use TheClinicDataStructures\DataStructures\Order\DSOrder;
use TheClinicDataStructures\DataStructures\Order\DSOrders;
use TheClinicDataStructures\DataStructures\Order\Regular\DSRegularOrder;
use TheClinicDataStructures\DataStructures\Order\Regular\DSRegularOrders;

class DatabaseRetrieveRegularOrdersTest extends TestCase
{
    use GetAuthenticatables;

    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testGetRegularOrdersByPriceByUser(): void
    {
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

        $operator = '=';
        $price = $regularOrder->price;

        $dsRegularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByPriceByUser($operator, $price, $dsAuthenticatable);

        $this->assertInstanceOf(DSRegularOrders::class, $dsRegularOrders);
        $this->assertGreaterThanOrEqual(1, count($dsRegularOrders));
        /** @var DSRegularOrder $dsRegularOrder */
        foreach ($dsRegularOrders as $dsRegularOrder) {
            $this->assertInstanceOf(DSRegularOrder::class, $dsRegularOrder);
            $this->assertEquals($price, $dsRegularOrder->getPrice());
        }

        $this->assertInstanceOf(DSOrders::class, $dsRegularOrders);
        $this->assertCount(1, $dsRegularOrders);
        $this->assertInstanceOf(DSOrder::class, $dsRegularOrders[0]);
        $this->assertEquals($price, $dsRegularOrders[0]->getPrice());

        $operator = '>';
        $price = 1;

        $dsRegularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByPriceByUser($operator, $price, $dsAuthenticatable);

        $this->assertInstanceOf(DSRegularOrders::class, $dsRegularOrders);

        /** @var DSRegularOrder $dsRegularOrder */
        foreach ($dsRegularOrders as $dsRegularOrder) {
            $this->assertInstanceOf(DSRegularOrder::class, $dsRegularOrder);
            $this->assertGreaterThan($price, $dsRegularOrder->getPrice());
        }
    }

    public function testGetRegularOrdersByPrice(): void
    {
        $authenticatable = $this->getAuthenticatable('patient');

        foreach ($authenticatable->user->orders as $order) {
            if (($regularOrder = $order->regularOrder) !== null) {
                $found = true;
                break;
            }
        }
        if (!isset($found)) {
            throw new \RuntimeException('Failure!!!', 500);
        }

        $lastOrderId = 5;
        $count = 10;
        $operator = '=';
        $price = $regularOrder->price;

        $dsRegularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByPrice($lastOrderId, $count, $operator, $price);

        $this->assertInstanceOf(DSRegularOrders::class, $dsRegularOrders);
        $this->assertGreaterThanOrEqual(1, count($dsRegularOrders));
        /** @var DSRegularOrder $dsRegularOrder */
        foreach ($dsRegularOrders as $dsRegularOrder) {
            $this->assertInstanceOf(DSRegularOrder::class, $dsRegularOrder);
            $this->assertEquals($price, $dsRegularOrder->getPrice());
        }

        $lastOrderId = 5;
        $count = 10;
        $operator = '>';
        $price = 1;

        $dsRegularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByPrice($lastOrderId, $count, $operator, $price);

        $this->assertInstanceOf(DSRegularOrders::class, $dsRegularOrders);

        /** @var DSRegularOrder $dsRegularOrder */
        foreach ($dsRegularOrders as $dsRegularOrder) {
            $this->assertInstanceOf(DSRegularOrder::class, $dsRegularOrder);
            $this->assertGreaterThan($price, $dsRegularOrder->getPrice());
        }
    }

    public function testGetRegularOrdersByTimeConsumptionByUser(): void
    {
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

        $operator = '=';
        $timeConsumption = $regularOrder->needed_time;

        $dsRegularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByTimeConsumptionByUser($operator, $timeConsumption, $dsAuthenticatable);

        $this->assertInstanceOf(DSRegularOrders::class, $dsRegularOrders);
        $this->assertGreaterThanOrEqual(1, count($dsRegularOrders));
        /** @var DSRegularOrder $dsRegularOrder */
        foreach ($dsRegularOrders as $dsRegularOrder) {
            $this->assertInstanceOf(DSRegularOrder::class, $dsRegularOrder);
            $this->assertEquals($timeConsumption, $dsRegularOrder->getNeededTime());
        }

        $operator = '>';
        $timeConsumption = 1;

        $dsRegularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByTimeConsumptionByUser($operator, $timeConsumption, $dsAuthenticatable);

        $this->assertInstanceOf(DSRegularOrders::class, $dsRegularOrders);
        $this->assertNotCount(0, $dsRegularOrders);

        /** @var DSRegularOrder $dsOrder */
        foreach ($dsRegularOrders as $dsOrder) {
            $this->assertInstanceOf(DSRegularOrder::class, $dsOrder);
            $this->assertGreaterThan($timeConsumption, $dsOrder->getNeededTime());
        }
    }

    public function testGetRegularOrdersByTimeConsumption(): void
    {
        $authenticatable = $this->getAuthenticatable('patient');
        foreach ($authenticatable->user->orders as $order) {
            if (($regularOrder = $order->regularOrder) !== null) {
                $found = true;
                break;
            }
        }
        if (!isset($found)) {
            throw new \RuntimeException('Failure!!!', 500);
        }

        $lastOrderId = 5;
        $count = 10;
        $operator = '=';
        $timeConsumption = $regularOrder->needed_time;

        $dsRegularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByTimeConsumption($count, $operator, $timeConsumption, $lastOrderId);

        $this->assertInstanceOf(DSRegularOrders::class, $dsRegularOrders);
        $this->assertGreaterThanOrEqual(1, count($dsRegularOrders));
        /** @var DSRegularOrder $dsRegularOrder */
        foreach ($dsRegularOrders as $dsRegularOrder) {
            $this->assertInstanceOf(DSRegularOrder::class, $dsRegularOrder);
            $this->assertEquals($timeConsumption, $dsRegularOrder->getNeededTime());
        }

        $lastOrderId = 5;
        $count = 10;
        $operator = '>';
        $timeConsumption = 1;

        $dsRegularOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByTimeConsumption($count, $operator, $timeConsumption, $lastOrderId);

        $this->assertInstanceOf(DSRegularOrders::class, $dsRegularOrders);
        $this->assertCount($count, $dsRegularOrders);

        /** @var DSRegularOrder $dsRegularOrder */
        foreach ($dsRegularOrders as $dsRegularOrder) {
            $this->assertInstanceOf(DSRegularOrder::class, $dsRegularOrder);
            $this->assertGreaterThan($timeConsumption, $dsRegularOrder->getNeededTime());
        }
    }

    public function testGetRegularOrdersByUser(): void
    {
        $authenticatable = $this->getAuthenticatable('patient');
        $dsUser = $authenticatable->getDataStructure();

        $dsOrders = (new DatabaseRetrieveRegularOrders)->getRegularOrdersByUser($dsUser);

        $this->assertInstanceOf(DSOrders::class, $dsOrders);
        $this->assertNotCount(0, $dsOrders);

        /** @var DSRegularOrder $dsOrder */
        foreach ($dsOrders as $dsOrder) {
            $this->assertInstanceOf(DSRegularOrder::class, $dsOrder);
        }
    }
}
