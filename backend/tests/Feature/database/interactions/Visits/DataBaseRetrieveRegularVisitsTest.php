<?php

namespace Tests\Feature\database\interactions\Visits;

use App\Auth\CheckAuthentication;
use App\Models\Auth\Patient;
use App\Models\Order\RegularOrder;
use App\Models\Order\Order;
use App\Models\Visit\RegularVisit;
use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;
use Database\Interactions\Visits\Retrieval\DataBaseRetrieveRegularVisits;
use Mockery;
use Mockery\MockInterface;

/**
 * @covers \Database\Interactions\Visits\Retrieval\DataBaseRetrieveRegularVisits
 */
class DataBaseRetrieveRegularVisitsTest extends TestCase
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
                if ($order->regularOrder !== null && count($order->regularOrder->regularVisits) !== 0) {
                    break 2;
                }
            }
            $safety++;
        }

        $regularVisits = (new DataBaseRetrieveRegularVisits)->getVisitsByUser($user, 'asc');

        $this->assertIsArray($regularVisits);
        $this->assertNotCount(0, $regularVisits);
        $this->assertContainsOnlyInstancesOf(RegularVisit::class, $regularVisits);
    }

    public function testGetVisitsByOrder(): void
    {
        $patient = Patient::query()->firstOrFail();
        $user = $patient->user;

        $safety = 0;
        while ($safety < 500) {
            /** @var Order $order */
            foreach ($user->orders as $order) {
                if ($order->regularOrder !== null && count($order->regularOrder->regularVisits) !== 0) {
                    /** @var RegularOrder $regularOrder */
                    $regularOrder = $order->regularOrder;
                    break 2;
                }
            }
            $safety++;
        }

        $regularVisits = (new DataBaseRetrieveRegularVisits)->getVisitsByOrder($regularOrder, 'asc');

        $this->assertIsArray($regularVisits);
        $this->assertNotCount(0, $regularVisits);
        $this->assertContainsOnlyInstancesOf(RegularVisit::class, $regularVisits);
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

        $regularVisits = (new DataBaseRetrieveRegularVisits($checkAuthentication))->getVisitsByTimestamp('patient', $operator, $timestamp, $sortByTimestamp, $count);

        $this->assertIsArray($regularVisits);
        $this->assertNotCount(0, $regularVisits);
        $this->assertContainsOnlyInstancesOf(RegularVisit::class, $regularVisits);

        $lastVisitTimestamp = array_pop($regularVisits)->visit_timestamp;

        $regularVisits = (new DataBaseRetrieveRegularVisits($checkAuthentication))->getVisitsByTimestamp('patient', $operator, $timestamp, $sortByTimestamp, $count, $lastVisitTimestamp);

        $this->assertIsArray($regularVisits);
        $this->assertNotCount(0, $regularVisits);
        $this->assertContainsOnlyInstancesOf(RegularVisit::class, $regularVisits);
    }

    public function testGetRegularVisitById(): void
    {
        $regularVisit = RegularVisit::query()->firstOrFail();

        $retrievedRegularVisit = (new DataBaseRetrieveRegularVisits)->getRegularVisitById($regularVisit->getKey());

        $this->assertInstanceOf(RegularVisit::class, $retrievedRegularVisit);
        $this->assertEquals($regularVisit->getKey(), $retrievedRegularVisit->getKey());
    }
}
