<?php

namespace Tests\Feature\database\interactions\Visits;

use App\DataStructures\Time\DSDateTimePeriods;
use App\DataStructures\Time\DSWeeklyTimePatterns;
use App\Models\Auth\Patient;
use App\Models\Visit\RegularVisit;
use App\PoliciesLogic\Visit\CustomVisit;
use App\PoliciesLogic\Visit\IFindVisit;
use App\PoliciesLogic\Visit\WeeklyVisit;
use Database\Interactions\Visits\Creation\DataBaseCreateRegularVisit;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * @covers \Database\Interactions\Visits\Creation\DataBaseCreateRegularVisit
 */
class DataBaseCreateRegularVisitTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testCreateRegularVisit(): void
    {
        try {
            DB::beginTransaction();

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

            /** @var IFindVisit|MockInterface $iFindVisit */
            $iFindVisit = Mockery::mock(IFindVisit::class);
            $iFindVisit->shouldReceive('findVisit')->once()->andReturn($timestamp = (new \DateTime)->modify("+100 days")->getTimestamp());

            $regularVisit = (new DataBaseCreateRegularVisit)->createRegularVisit($regularOrder, $iFindVisit);

            $this->assertInstanceOf(RegularVisit::class, $regularVisit);
            $this->assertDatabaseHas($regularVisit->getTable(), ['visit_timestamp' => $timestamp]);

            DB::rollback();

            $this->assertDatabaseMissing($regularVisit->getTable(), ['visit_timestamp' => $timestamp]);

            // ------------------------------------------------------------------------------------------------------------------------------------------------

            DB::beginTransaction();

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

            /** @var WeeklyVisit|MockInterface $iFindVisit */
            $iFindVisit = Mockery::mock(WeeklyVisit::class);
            $iFindVisit->shouldReceive('findVisit')->once()->andReturn($timestamp = (new \DateTime)->modify("+100 days")->getTimestamp());
            $iFindVisit->shouldReceive('getDSTimePatterns')->once()->andReturn(new DSWeeklyTimePatterns('Monday'));

            $regularVisit = (new DataBaseCreateRegularVisit)->createRegularVisit($regularOrder, $iFindVisit);

            $this->assertInstanceOf(RegularVisit::class, $regularVisit);
            $this->assertDatabaseHas($regularVisit->getTable(), ['visit_timestamp' => $timestamp]);

            DB::rollback();

            $this->assertDatabaseMissing($regularVisit->getTable(), ['visit_timestamp' => $timestamp]);

            // ------------------------------------------------------------------------------------------------------------------------------------------------

            DB::beginTransaction();

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

            /** @var CustomVisit|MockInterface $iFindVisit */
            $iFindVisit = Mockery::mock(CustomVisit::class);
            $iFindVisit->shouldReceive('findVisit')->once()->andReturn($timestamp = (new \DateTime)->modify("+100 days")->getTimestamp());
            $iFindVisit->shouldReceive('getDSDateTimePeriods')->once()->andReturn(new DSDateTimePeriods);

            $regularVisit = (new DataBaseCreateRegularVisit)->createRegularVisit($regularOrder, $iFindVisit);

            $this->assertInstanceOf(RegularVisit::class, $regularVisit);
            $this->assertDatabaseHas($regularVisit->getTable(), ['visit_timestamp' => $timestamp]);

            DB::rollback();

            $this->assertDatabaseMissing($regularVisit->getTable(), ['visit_timestamp' => $timestamp]);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
