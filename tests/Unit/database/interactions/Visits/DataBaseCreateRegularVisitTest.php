<?php

namespace Tests\Unit\database\interactions\Visits;

use App\Models\BusinessDefault;
use App\Models\Order\RegularOrder;
use App\Models\Order\Order;
use App\Models\Visit\RegularVisit;
use Database\Interactions\Visits\DataBaseCreateRegularVisit;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use App\PoliciesLogic\Visit\FastestVisit;
use App\PoliciesLogicDataStructures\DataStructures\Visit\Regular\DSRegularVisit;

class DataBaseCreateRegularVisitTest extends TestCase
{
    use GetAuthenticatables;

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

            $safety = 0;
            while ($safety < 500) {
                $userRole = $this->getAuthenticatable('patient');
                $user = $userRole->user;
                $dsUser = $userRole->getDataStructure();

                /** @var Order $order */
                foreach ($user->orders as $order) {
                    /** @var RegularOrder $regularOrder */
                    if (($regularOrder = $order->regularOrder) !== null) {
                        $dsRegularOrder = $regularOrder->getDSRegularOrder();
                        break 2;
                    }
                }

                $safety++;
            }
            if (!isset($dsRegularOrder)) {
                throw new ModelNotFoundException('', 404);
            }

            $now = new \DateTime();
            $futureVisits = RegularVisit::query()
                ->orderBy('visit_timestamp', 'asc')
                ->where('visit_timestamp', '>=', $now)
                ->get()
                ->all()
                //
            ;
            $futureVisits = RegularVisit::getDSRegularVisits($futureVisits, 'ASC');

            $iFindVisit = new FastestVisit(
                new \DateTime,
                $dsRegularOrder->getNeededTime(),
                $futureVisits,
                $dsWoekSchedule = BusinessDefault::first()->work_schedule,
                $dsDownTimes = BusinessDefault::first()->down_times,
            );

            $dsRegularVisit = (new DataBaseCreateRegularVisit)->createRegularVisit($dsRegularOrder, $dsUser, $iFindVisit);
            $this->assertInstanceOf(DSRegularVisit::class, $dsRegularVisit);
        } finally {
            DB::rollback();
        }
    }
}
