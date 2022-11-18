<?php

namespace Tests\Unit\database\interactions\Visits;

use App\Auth\CheckAuthentication;
use App\Models\Auth\Patient;
use App\Models\Order\LaserOrder;
use App\Models\Order\Order;
use App\Models\Visit\LaserVisit;
use Database\Interactions\Visits\Retrieval\DataBaseRetrieveLaserVisits;
use Faker\Factory;
use Faker\Generator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * @covers \Database\Interactions\Visits\Retrieval\DataBaseRetrieveLaserVisits
 */
class DataBaseRetrieveLaserVisitsTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testGetVisitsByUser(): void
    {
        $patient = Patient::query()->firstOrFail();
        $user = $patient->user;

        $safety = 0;
        while ($safety < 500) {
            /** @var Order $order */
            foreach ($user->orders as $order) {
                if ($order->laserOrder !== null && count($order->laserOrder->laserVisits) !== 0) {
                    break 2;
                }
            }
            $safety++;
        }

        $laserVisits = (new DataBaseRetrieveLaserVisits)->getVisitsByUser($user, 'asc');

        $this->assertIsArray($laserVisits);
        $this->assertNotCount(0, $laserVisits);
        $this->assertContainsOnlyInstancesOf(LaserVisit::class, $laserVisits);
    }

    public function testGetVisitsByOrder(): void
    {
        $patient = Patient::query()->firstOrFail();
        $user = $patient->user;

        $safety = 0;
        while ($safety < 500) {
            /** @var Order $order */
            foreach ($user->orders as $order) {
                if ($order->laserOrder !== null && count($order->laserOrder->laserVisits) !== 0) {
                    /** @var LaserOrder $laserOrder */
                    $laserOrder = $order->laserOrder;
                    break 2;
                }
            }
            $safety++;
        }

        $laserVisits = (new DataBaseRetrieveLaserVisits)->getVisitsByOrder($laserOrder, 'asc');

        $this->assertIsArray($laserVisits);
        $this->assertNotCount(0, $laserVisits);
        $this->assertContainsOnlyInstancesOf(LaserVisit::class, $laserVisits);
    }

    public function testGetVisitsByTimestamp(): void
    {
        $user = Patient::query()->firstOrFail()->user;
        /** @var CheckAuthentication|MockInterface $checkAuthentication */
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication->shouldReceive('getAuthenticated')->andReturn($user);

        $operator = '>=';
        $timestamp = (new \DateTime('2022-4-9 00:00:00'))->getTimestamp();
        $sortByTimestamp = 'asc';
        $count = 5;
        $lastVisitTimestamp = null;

        $laserVisits = (new DataBaseRetrieveLaserVisits($checkAuthentication))->getVisitsByTimestamp('patient', $operator, $timestamp, $sortByTimestamp, $count);

        $this->assertIsArray($laserVisits);
        $this->assertNotCount(0, $laserVisits);
        $this->assertContainsOnlyInstancesOf(LaserVisit::class, $laserVisits);

        $lastVisitTimestamp = array_pop($laserVisits)->visit_timestamp;

        $laserVisits = (new DataBaseRetrieveLaserVisits($checkAuthentication))->getVisitsByTimestamp('patient', $operator, $timestamp, $sortByTimestamp, $count, $lastVisitTimestamp);

        $this->assertIsArray($laserVisits);
        $this->assertNotCount(0, $laserVisits);
        $this->assertContainsOnlyInstancesOf(LaserVisit::class, $laserVisits);
    }

    public function testGetLaserVisitById(): void
    {
        $laserVisit = LaserVisit::query()->firstOrFail();

        $retrievedLaserVisit = (new DataBaseRetrieveLaserVisits)->getLaserVisitById($laserVisit->getKey());

        $this->assertInstanceOf(LaserVisit::class, $retrievedLaserVisit);
        $this->assertEquals($laserVisit->getKey(), $retrievedLaserVisit->getKey());
    }
}
