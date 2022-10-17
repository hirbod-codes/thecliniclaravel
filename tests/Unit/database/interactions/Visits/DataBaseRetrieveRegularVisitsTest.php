<?php

namespace Tests\Unit\database\interactions\Visits;

use App\Models\Order\RegularOrder;
use App\Models\Order\Order;
use Database\Interactions\Visits\DataBaseRetrieveRegularVisits;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use App\PoliciesLogicDataStructures\DataStructures\Visit\Regular\DSRegularVisits;

class DataBaseRetrieveRegularVisitsTest extends TestCase
{
    use GetAuthenticatables;

    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testGetVisitsByUser(): void
    {
        $safety = 0;
        while ($safety < 500) {
            $targetUserRole = $this->getAuthenticatable('patient');
            $dsTargetUser = $targetUserRole->getDataStructure();
            $targetUser = $targetUserRole->user;

            /** @var Order $order */
            foreach ($targetUser->orders as $order) {
                if (count($order->regularOrder->regularVisits) !== 0) {
                    break 2;
                }
            }
            $safety++;
        }

        $dsRegularVisits = (new DataBaseRetrieveRegularVisits)->getVisitsByUser($dsTargetUser, 'asc');
        $this->assertInstanceOf(DSRegularVisits::class, $dsRegularVisits);
        $this->assertNotCount(0, $dsRegularVisits);
    }

    public function testGetVisitsByOrder(): void
    {
        $safety = 0;
        while ($safety < 500) {
            $targetUserRole = $this->getAuthenticatable('patient');
            $dsTargetUser = $targetUserRole->getDataStructure();
            $targetUser = $targetUserRole->user;

            /** @var Order $order */
            foreach ($targetUser->orders as $order) {
                if (count($order->regularOrder->regularVisits) !== 0) {
                    /** @var RegularOrder $regularOrder */
                    $regularOrder = $order->regularOrder;
                    $dsRegularOrder = $regularOrder->getDSRegularOrder();
                    break 2;
                }
            }
            $safety++;
        }
        if (!isset($dsRegularOrder)) {
            throw new ModelNotFoundException('', 404);
        }

        $dsRegularVisits = (new DataBaseRetrieveRegularVisits)->getVisitsByOrder($dsTargetUser, $dsRegularOrder, 'asc');
        $this->assertInstanceOf(DSRegularVisits::class, $dsRegularVisits);
        $this->assertNotCount(0, $dsRegularVisits);
    }

    public function testGetVisitsByTimestamp(): void
    {
        $operator = '>=';
        $timestamp = (new \DateTime('2022-4-9 00:00:00'))->getTimestamp();
        $sortByTimestamp = 'asc';

        $dsRegularVisits = (new DataBaseRetrieveRegularVisits)->getVisitsByTimestamp($operator, $timestamp, $sortByTimestamp);
        $this->assertInstanceOf(DSRegularVisits::class, $dsRegularVisits);
        $this->assertNotCount(0, $dsRegularVisits);
    }
}
