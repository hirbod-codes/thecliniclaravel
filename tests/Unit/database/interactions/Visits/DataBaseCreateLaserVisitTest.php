<?php

namespace Tests\Unit\database\interactions\Visits;

use App\Models\BusinessDefault;
use App\Models\Order\LaserOrder;
use App\Models\Order\Order;
use App\Models\Visit\LaserVisit;
use Database\Interactions\Visits\DataBaseCreateLaserVisit;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use App\PoliciesLogic\Visit\FastestVisit;
use App\PoliciesLogicDataStructures\DataStructures\Visit\Laser\DSLaserVisit;

class DataBaseCreateLaserVisitTest extends TestCase
{
    use GetAuthenticatables;

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

            $safety = 0;
            while ($safety < 500) {
                $userRole = $this->getAuthenticatable('patient');
                $user = $userRole->user;
                $dsUser = $userRole->getDataStructure();

                /** @var Order $order */
                foreach ($user->orders as $order) {
                    /** @var LaserOrder $laserOrder */
                    if (($laserOrder = $order->laserOrder) !== null) {
                        $dsLaserOrder = $laserOrder->getDSLaserOrder();
                        break 2;
                    }
                }

                $safety++;
            }
            if (!isset($dsLaserOrder)) {
                throw new ModelNotFoundException('', 404);
            }

            $now = new \DateTime();
            $futureVisits = LaserVisit::query()
                ->orderBy('visit_timestamp', 'asc')
                ->where('visit_timestamp', '>=', $now)
                ->get()
                ->all()
                //
            ;
            $futureVisits = LaserVisit::getDSLaserVisits($futureVisits, 'ASC');

            $iFindVisit = new FastestVisit(
                new \DateTime,
                $dsLaserOrder->getNeededTime(),
                $futureVisits,
                $dsWoekSchedule = BusinessDefault::first()->work_schedule,
                $dsDownTimes = BusinessDefault::first()->down_times,
            );

            $dsLaserVisit = (new DataBaseCreateLaserVisit)->createLaserVisit($dsLaserOrder, $dsUser, $iFindVisit);
            $this->assertInstanceOf(DSLaserVisit::class, $dsLaserVisit);
            $this->assertDatabaseHas((new LaserVisit)->getTable(), ['visit_timestamp' => $iFindVisit->findVisit()]);
        } finally {
            DB::rollback();
        }
    }
}
