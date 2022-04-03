<?php

namespace Tests\Unit\database\interactions\Orders\Retrieval;

use Database\Interactions\Orders\Retrieval\DatabaseRetrieveLaserOrders;
use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use TheClinicDataStructures\DataStructures\Order\DSOrder;
use TheClinicDataStructures\DataStructures\Order\DSOrders;
use TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrder;
use TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrders;

class DatabaseRetrieveLaserOrdersTest extends TestCase
{
    use GetAuthenticatables;

    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testGetLaserOrdersByPriceByUser(): void
    {
        $authenticatable = $this->getAuthenticatable('patient');
        $dsAuthenticatable = $authenticatable->getDataStructure();

        foreach ($authenticatable->user->orders as $order) {
            if (($laserOrder = $order->laserOrder) !== null) {
                $found = true;
                break;
            }
        }
        if (!isset($found)) {
            throw new \RuntimeException('Failure!!!', 500);
        }

        $operator = '=';
        $price = $laserOrder->price;

        $dsLaserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByPriceByUser($operator, $price, $dsAuthenticatable);

        $this->assertInstanceOf(DSLaserOrders::class, $dsLaserOrders);
        $this->assertNotCount(0, $dsLaserOrders);
        foreach ($dsLaserOrders as $dsLaserOrder) {
            $this->assertInstanceOf(DSLaserOrder::class, $dsLaserOrder);
            $this->assertEquals($price, $dsLaserOrder->getPrice());
        }

        $operator = '>';
        $price = 1;

        $dsLaserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByPriceByUser($operator, $price, $dsAuthenticatable);

        $this->assertInstanceOf(DSOrders::class, $dsLaserOrders);
        $this->assertNotCount(0, $dsLaserOrders);

        /** @var DSLaserOrder $dsOrder */
        foreach ($dsLaserOrders as $dsOrder) {
            $this->assertInstanceOf(DSLaserOrder::class, $dsOrder);
            $this->assertGreaterThan($price, $dsOrder->getPrice());
        }
    }

    public function testGetLaserOrdersByPrice(): void
    {
        $authenticatable = $this->getAuthenticatable('patient');

        foreach ($authenticatable->user->orders as $order) {
            if (($laserOrder = $order->laserOrder) !== null) {
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
        $price = $laserOrder->price;

        $dsLaserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByPrice($lastOrderId, $count, $operator, $price);

        $this->assertInstanceOf(DSLaserOrders::class, $dsLaserOrders);
        $this->assertGreaterThanOrEqual(1, count($dsLaserOrders));
        foreach ($dsLaserOrders as $dsLaserOrder) {
            $this->assertInstanceOf(DSLaserOrder::class, $dsLaserOrder);
            $this->assertEquals($price, $dsLaserOrder->getPrice());
        }

        $lastOrderId = 5;
        $count = 10;
        $operator = '>';
        $price = 1;

        $dsLaserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByPrice($lastOrderId, $count, $operator, $price);

        $this->assertInstanceOf(DSOrders::class, $dsLaserOrders);

        /** @var DSLaserOrder $dsOrder */
        foreach ($dsLaserOrders as $dsOrder) {
            $this->assertInstanceOf(DSLaserOrder::class, $dsOrder);
            $this->assertGreaterThan($price, $dsOrder->getPrice());
        }
    }

    public function testGetLaserOrdersByTimeConsumptionByUser(): void
    {
        $authenticatable = $this->getAuthenticatable('patient');
        $dsAuthenticatable = $authenticatable->getDataStructure();

        foreach ($authenticatable->user->orders as $order) {
            if (($laserOrder = $order->laserOrder) !== null) {
                $found = true;
                break;
            }
        }
        if (!isset($found)) {
            throw new \RuntimeException('Failure!!!', 500);
        }

        $operator = '=';
        $timeConsumption = $laserOrder->needed_time;

        $dsLaserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByTimeConsumptionByUser($operator, $timeConsumption, $dsAuthenticatable);

        $this->assertInstanceOf(DSLaserOrders::class, $dsLaserOrders);
        $this->assertGreaterThanOrEqual(1, count($dsLaserOrders));
        foreach ($dsLaserOrders as $dsLaserOrder) {
            $this->assertInstanceOf(DSLaserOrder::class, $dsLaserOrder);
            $this->assertEquals($timeConsumption, $dsLaserOrder->getNeededTime());
        }

        $operator = '>';
        $timeConsumption = 1;

        $dsLaserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByTimeConsumptionByUser($operator, $timeConsumption, $dsAuthenticatable);

        $this->assertInstanceOf(DSLaserOrders::class, $dsLaserOrders);

        /** @var DSLaserOrder $dsOrder */
        foreach ($dsLaserOrders as $dsOrder) {
            $this->assertInstanceOf(DSLaserOrder::class, $dsOrder);
            $this->assertGreaterThan($timeConsumption, $dsOrder->getNeededTime());
        }
    }

    public function testGetLaserOrdersByTimeConsumption(): void
    {
        $authenticatable = $this->getAuthenticatable('patient');

        foreach ($authenticatable->user->orders as $order) {
            if (($laserOrder = $order->laserOrder) !== null) {
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
        $timeConsumption = $laserOrder->needed_time;

        $dsLaserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByTimeConsumption($count, $operator, $timeConsumption, $lastOrderId);

        $this->assertInstanceOf(DSLaserOrders::class, $dsLaserOrders);
        $this->assertGreaterThanOrEqual(1, count($dsLaserOrders));
        foreach ($dsLaserOrders as $dsLaserOrder) {
            $this->assertInstanceOf(DSLaserOrder::class, $dsLaserOrder);
            $this->assertEquals($timeConsumption, $dsLaserOrder->getNeededTime());
        }

        $lastOrderId = 5;
        $count = 10;
        $operator = '>';
        $timeConsumption = 1;

        $dsLaserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByTimeConsumption($count, $operator, $timeConsumption, $lastOrderId);

        $this->assertInstanceOf(DSOrders::class, $dsLaserOrders);

        /** @var DSLaserOrder $dsOrder */
        foreach ($dsLaserOrders as $dsOrder) {
            $this->assertInstanceOf(DSLaserOrder::class, $dsOrder);
            $this->assertGreaterThan($timeConsumption, $dsOrder->getNeededTime());
        }
    }

    public function testGetLaserOrdersByUser(): void
    {
        $user = $this->getAuthenticatable('patient');
        $dsUser = $user->getDataStructure();

        $dsLaserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByUser($dsUser);

        $this->assertInstanceOf(DSOrders::class, $dsLaserOrders);
        $this->assertNotCount(0, $dsLaserOrders);

        /** @var DSLaserOrder $dsOrder */
        foreach ($dsLaserOrders as $dsOrder) {
            $this->assertInstanceOf(DSLaserOrder::class, $dsOrder);
        }
    }
}
