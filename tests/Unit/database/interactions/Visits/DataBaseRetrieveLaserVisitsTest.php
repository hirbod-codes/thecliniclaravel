<?php

namespace Tests\Unit\database\interactions\Visits;

use App\Models\Order\LaserOrder;
use App\Models\Order\Order;
use Database\Interactions\Visits\DataBaseRetrieveLaserVisits;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use App\PoliciesLogicDataStructures\DataStructures\Visit\Laser\DSLaserVisits;

class DataBaseRetrieveLaserVisitsTest extends TestCase
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
                if ($order->laserOrder !== null && count($order->laserOrder->laserVisits) !== 0) {
                    break 2;
                }
            }
            $safety++;
        }

        $dsLaserVisits = (new DataBaseRetrieveLaserVisits)->getVisitsByUser($dsTargetUser, 'asc');
        $this->assertInstanceOf(DSLaserVisits::class, $dsLaserVisits);
        $this->assertNotCount(0, $dsLaserVisits);
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
                if ($order->laserOrder !== null && count($order->laserOrder->laserVisits) !== 0) {
                    /** @var LaserOrder $laserOrder */
                    $laserOrder = $order->laserOrder;
                    $dsLaserOrder = $laserOrder->getDSLaserOrder();
                    break 2;
                }
            }
            $safety++;
        }
        if (!isset($dsLaserOrder)) {
            throw new ModelNotFoundException('', 404);
        }

        $dsLaserVisits = (new DataBaseRetrieveLaserVisits)->getVisitsByOrder($dsTargetUser, $dsLaserOrder, 'asc');
        $this->assertInstanceOf(DSLaserVisits::class, $dsLaserVisits);
        $this->assertNotCount(0, $dsLaserVisits);
    }

    public function testGetVisitsByTimestamp(): void
    {
        $operator = '>=';
        $timestamp = (new \DateTime('2022-4-9 00:00:00'))->getTimestamp();
        $sortByTimestamp = 'asc';

        $dsLaserVisits = (new DataBaseRetrieveLaserVisits)->getVisitsByTimestamp($operator, $timestamp, $sortByTimestamp);
        $this->assertInstanceOf(DSLaserVisits::class, $dsLaserVisits);
        $this->assertNotCount(0, $dsLaserVisits);
    }
}
