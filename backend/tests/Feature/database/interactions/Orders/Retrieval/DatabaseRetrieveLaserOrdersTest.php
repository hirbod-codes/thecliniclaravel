<?php

namespace Tests\Feature\database\interactions\Orders\Retrieval;

use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSPart;
use App\DataStructures\Order\DSParts;
use App\Models\Auth\Patient;
use App\Models\Order\LaserOrder;
use App\Models\Package\Package;
use App\Models\Part\Part;
use Database\Interactions\Orders\Retrieval\DatabaseRetrieveLaserOrders;
use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;
use Illuminate\Support\Arr;

/**
 * @covers \Database\Interactions\Orders\Retrieval\DatabaseRetrieveLaserOrders
 */
class DatabaseRetrieveLaserOrdersTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testGetLaserOrdersByPriceByUser(): void
    {
        $patient = Patient::query()->firstOrFail();
        $user = $patient->user;

        foreach ($user->orders as $order) {
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

        $laserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByPriceByUser($operator, $price, $user);

        $this->assertIsArray($laserOrders);
        $this->assertContainsOnlyInstancesOf(LaserOrder::class, $laserOrders);
        $this->assertNotCount(0, $laserOrders);
        foreach ($laserOrders as $laserOrder) {
            $this->assertEquals($price, $laserOrder->price);
        }

        $operator = '>';
        $price = 1;

        $laserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByPriceByUser($operator, $price, $user);

        $this->assertIsArray($laserOrders);
        $this->assertContainsOnlyInstancesOf(LaserOrder::class, $laserOrders);
        $this->assertNotCount(0, $laserOrders);
        foreach ($laserOrders as $laserOrder) {
            $this->assertGreaterThan($price, $laserOrder->price);
        }
    }

    public function testGetLaserOrdersByPrice(): void
    {
        $patient = Patient::query()->firstOrFail();
        $user = $patient->user;

        foreach ($user->orders as $order) {
            if (($laserOrder = $order->laserOrder) !== null) {
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
        $price = $laserOrder->price;
        $roleName = 'patient';

        $laserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByPrice($roleName, $lastOrderId, $count, $operator, $price);

        $this->assertIsArray($laserOrders);
        $this->assertContainsOnlyInstancesOf(LaserOrder::class, $laserOrders);
        $this->assertLessThanOrEqual($count, count($laserOrders));
        foreach ($laserOrders as $laserOrder) {
            $this->assertEquals($price, $laserOrder->price);
        }

        $lastOrderId = null;
        $count = 10;
        $operator = '>';
        $price = 1;
        $roleName = 'patient';

        $laserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByPrice($roleName, $lastOrderId, $count, $operator, $price);

        $this->assertIsArray($laserOrders);
        $this->assertContainsOnlyInstancesOf(LaserOrder::class, $laserOrders);
        $this->assertCount($count, $laserOrders);
        foreach ($laserOrders as $laserOrder) {
            $this->assertGreaterThan($price, $laserOrder->price);
        }

        $lastOrderId = array_pop($laserOrders)->getKey();

        $laserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByPrice($roleName, $lastOrderId, $count, $operator, $price);

        $this->assertIsArray($laserOrders);
        $this->assertContainsOnlyInstancesOf(LaserOrder::class, $laserOrders);
        $this->assertCount($count, $laserOrders);
        $this->assertLessThan($lastOrderId, $laserOrders[0]->getKey());
        foreach ($laserOrders as $laserOrder) {
            $this->assertGreaterThan($price, $laserOrder->price);
        }
    }

    public function testGetLaserOrdersByTimeConsumptionByUser(): void
    {
        $patient = Patient::query()->firstOrFail();
        $user = $patient->user;

        foreach ($user->orders as $order) {
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

        $laserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByTimeConsumptionByUser($operator, $timeConsumption, $user);

        $this->assertIsArray($laserOrders);
        $this->assertContainsOnlyInstancesOf(LaserOrder::class, $laserOrders);
        $this->assertGreaterThanOrEqual(1, count($laserOrders));
        foreach ($laserOrders as $laserOrder) {
            $this->assertEquals($timeConsumption, $laserOrder->needed_time);
        }

        $operator = '>';
        $timeConsumption = 1;

        $laserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByTimeConsumptionByUser($operator, $timeConsumption, $user);

        $this->assertIsArray($laserOrders);
        $this->assertContainsOnlyInstancesOf(LaserOrder::class, $laserOrders);
        foreach ($laserOrders as $laserOrder) {
            $this->assertGreaterThan($timeConsumption, $laserOrder->needed_time);
        }
    }

    public function testGetLaserOrdersByTimeConsumption(): void
    {
        $patient = Patient::query()->firstOrFail();
        $user = $patient->user;

        foreach ($user->orders as $order) {
            if (($laserOrder = $order->laserOrder) !== null) {
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
        $timeConsumption = $laserOrder->needed_time;

        $laserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByTimeConsumption($roleName, $count, $operator, $timeConsumption, $lastOrderId);

        $this->assertIsArray($laserOrders);
        $this->assertContainsOnlyInstancesOf(LaserOrder::class, $laserOrders);
        $this->assertLessThanOrEqual($count, count($laserOrders));
        foreach ($laserOrders as $laserOrder) {
            $this->assertEquals($timeConsumption, $laserOrder->needed_time);
        }

        $lastOrderId = null;
        $count = 10;
        $operator = '>';
        $timeConsumption = 1;

        $laserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByTimeConsumption($roleName, $count, $operator, $timeConsumption, $lastOrderId);

        $this->assertIsArray($laserOrders);
        $this->assertContainsOnlyInstancesOf(LaserOrder::class, $laserOrders);
        $this->assertCount($count, $laserOrders);
        foreach ($laserOrders as $laserOrder) {
            $this->assertGreaterThan($timeConsumption, $laserOrder->needed_time);
        }

        $lastOrderId = array_pop($laserOrders)->getKey();

        $laserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByTimeConsumption($roleName, $count, $operator, $timeConsumption, $lastOrderId);

        $this->assertIsArray($laserOrders);
        $this->assertContainsOnlyInstancesOf(LaserOrder::class, $laserOrders);
        $this->assertCount($count, $laserOrders);
        $this->assertLessThan($lastOrderId, $laserOrders[0]->getKey());
        foreach ($laserOrders as $laserOrder) {
            $this->assertGreaterThan($timeConsumption, $laserOrder->needed_time);
        }
    }

    public function testGetLaserOrdersByUser(): void
    {
        $patient = Patient::query()->firstOrFail();
        $user = $patient->user;

        $laserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrdersByUser($user);

        $this->assertIsArray($laserOrders);
        $this->assertNotCount(0, $laserOrders);
        $this->assertContainsOnlyInstancesOf(LaserOrder::class, $laserOrders);
        /** @var LaserOrder $laserOrder */
        foreach ($laserOrders as $laserOrder) {
            $this->assertEquals($user->getKey(), $laserOrder->order->user->getKey());
        }
    }

    public function testCollectDSPartsFromNames(): void
    {
        $gender = $this->faker->randomElement(['Male', 'Female']);
        $maxId = Part::query()->orderBy((new Part)->getKeyName(), 'desc')->firstOrFail()->{(new Part)->getKeyName()};
        $partsName = Arr::flatten(Part::query()->where('gender', '=', $gender)->take($this->faker->numberBetween(1, $maxId))->get(['name'])->toArray());

        $dsParts = (new DatabaseRetrieveLaserOrders)->collectDSPartsFromNames($partsName, $gender);

        $this->assertInstanceOf(DSParts::class, $dsParts);
        $this->assertEquals(count($partsName), count($dsParts));
        /** @var DSPart $dsPart */
        foreach ($dsParts as $dsPart) {
            $this->assertContains($dsPart->getName(), $partsName);
        }
    }

    public function testCollectDSPacakgesFromNames(): void
    {
        $gender = $this->faker->randomElement(['Male', 'Female']);
        $maxId = Package::query()->orderBy((new Package)->getKeyName(), 'desc')->firstOrFail()->{(new Package)->getKeyName()};
        $packagesName = Arr::flatten(Package::query()->where('gender', '=', $gender)->take($this->faker->numberBetween(1, $maxId))->get(['name'])->toArray());

        $dsPackages = (new DatabaseRetrieveLaserOrders)->collectDSPackagesFromNames($packagesName, $gender);

        $this->assertInstanceOf(DSPackages::class, $dsPackages);
        $this->assertEquals(count($packagesName), count($dsPackages));
        /** @var DSPackage $dsPackage */
        foreach ($dsPackages as $dsPackage) {
            $this->assertContains($dsPackage->getName(), $packagesName);
        }
    }

    public function testGetLaserOrderById(): void
    {
        $laserOrder = LaserOrder::query()->firstOrFail();

        $foundedLaserOrder = (new DatabaseRetrieveLaserOrders)->getLaserOrderById($laserOrder->getKey());

        $this->assertInstanceOf(LaserOrder::class, $foundedLaserOrder);
        $this->assertEquals($laserOrder->getKey(), $foundedLaserOrder->getKey());
    }

    public function testGetLaserOrders(): void
    {
        $lastOrderId = null;
        $count = 10;
        $roleName = 'patient';

        $laserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrders($roleName, $count, $lastOrderId);

        $this->assertIsArray($laserOrders);
        $this->assertContainsOnlyInstancesOf(LaserOrder::class, $laserOrders);
        $this->assertCount($count, $laserOrders);

        foreach ($laserOrders as $laserOrder) {
            $t = $laserOrder->order->user->authenticatableRole->role->roleName->name;
            $this->assertEquals($roleName, $t);
        }

        $lastOrderId = array_pop($laserOrders)->getKey();

        $laserOrders = (new DatabaseRetrieveLaserOrders)->getLaserOrders($roleName, $count, $lastOrderId);

        $this->assertIsArray($laserOrders);
        $this->assertContainsOnlyInstancesOf(LaserOrder::class, $laserOrders);
        $this->assertCount($count, $laserOrders);
        $this->assertLessThan($lastOrderId, $laserOrders[0]->getKey());

        foreach ($laserOrders as $laserOrder) {
            $t = $laserOrder->order->user->authenticatableRole->role->roleName->name;
            $this->assertEquals($roleName, $t);
        }
    }
}
