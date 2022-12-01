<?php

namespace Tests\Feature\database\interactions\Visits;

use App\DataStructures\Time\DSDateTimePeriods;
use App\DataStructures\Time\DSWeeklyTimePatterns;
use App\Models\Auth\Patient;
use App\Models\Visit\LaserVisit;
use App\PoliciesLogic\Visit\CustomVisit;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\PoliciesLogic\Visit\IFindVisit;
use App\PoliciesLogic\Visit\WeeklyVisit;
use Database\Interactions\Visits\Creation\DataBaseCreateLaserVisit;
use Mockery;
use Mockery\MockInterface;

/**
 * @covers \Database\Interactions\Visits\Creation\DataBaseCreateLaserVisit
 */
class DataBaseCreateLaserVisitTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testCreateLaserVisit(): void
    {
        try {
            DB::beginTransaction();

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

            /** @var IFindVisit|MockInterface $iFindVisit */
            $iFindVisit = Mockery::mock(IFindVisit::class);
            $iFindVisit->shouldReceive('findVisit')->once()->andReturn($timestamp = (new \DateTime)->modify("+100 days")->getTimestamp());

            $laserVisit = (new DataBaseCreateLaserVisit)->createLaserVisit($laserOrder, $iFindVisit);

            $this->assertInstanceOf(LaserVisit::class, $laserVisit);
            $this->assertDatabaseHas($laserVisit->getTable(), ['visit_timestamp' => $timestamp]);

            DB::rollback();

            $this->assertDatabaseMissing($laserVisit->getTable(), ['visit_timestamp' => $timestamp]);

            // ------------------------------------------------------------------------------------------------------------------------------------------------

            DB::beginTransaction();

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

            /** @var WeeklyVisit|MockInterface $iFindVisit */
            $iFindVisit = Mockery::mock(WeeklyVisit::class);
            $iFindVisit->shouldReceive('findVisit')->once()->andReturn($timestamp = (new \DateTime)->modify("+100 days")->getTimestamp());
            $iFindVisit->shouldReceive('getDSTimePatterns')->once()->andReturn(new DSWeeklyTimePatterns('Monday'));

            $laserVisit = (new DataBaseCreateLaserVisit)->createLaserVisit($laserOrder, $iFindVisit);

            $this->assertInstanceOf(LaserVisit::class, $laserVisit);
            $this->assertDatabaseHas($laserVisit->getTable(), ['visit_timestamp' => $timestamp]);

            DB::rollback();

            $this->assertDatabaseMissing($laserVisit->getTable(), ['visit_timestamp' => $timestamp]);

            // ------------------------------------------------------------------------------------------------------------------------------------------------

            DB::beginTransaction();

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

            /** @var CustomVisit|MockInterface $iFindVisit */
            $iFindVisit = Mockery::mock(CustomVisit::class);
            $iFindVisit->shouldReceive('findVisit')->once()->andReturn($timestamp = (new \DateTime)->modify("+100 days")->getTimestamp());
            $iFindVisit->shouldReceive('getDSDateTimePeriods')->once()->andReturn(new DSDateTimePeriods);

            $laserVisit = (new DataBaseCreateLaserVisit)->createLaserVisit($laserOrder, $iFindVisit);

            $this->assertInstanceOf(LaserVisit::class, $laserVisit);
            $this->assertDatabaseHas($laserVisit->getTable(), ['visit_timestamp' => $timestamp]);

            DB::rollback();

            $this->assertDatabaseMissing($laserVisit->getTable(), ['visit_timestamp' => $timestamp]);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
