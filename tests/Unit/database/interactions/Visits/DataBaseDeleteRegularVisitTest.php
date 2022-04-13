<?php

namespace Tests\Unit\database\interactions\Visits;

use App\Models\Order\RegularOrder;
use App\Models\Visit\RegularVisit;
use Database\Interactions\Visits\DataBaseDeleteRegularVisit;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;

class DataBaseDeleteRegularVisitTest extends TestCase
{
    use GetAuthenticatables;

    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testDeleteRegularVisit(): void
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
                    /**
                     *  @var RegularOrder $regularOrder
                     *  @var RegularVisit $regularVisit
                     *  @var RegularVisit[] $regularVisits
                     */
                    if (($regularOrder = $order->regularOrder) !== null && count($regularVisits = $regularOrder->regularVisits) !== 0) {
                        $dsRegularOrder = $regularOrder->getDSRegularOrder();
                        $regularVisit = $regularVisits[0];
                        $dsRegularVisit = $regularVisits[0]->getDSRegularVisit();
                        break 2;
                    }
                }

                $safety++;
            }
            if (!isset($dsRegularVisit)) {
                throw new ModelNotFoundException('', 404);
            }

            $result = (new DataBaseDeleteRegularVisit)->deleteRegularVisit($dsRegularVisit, $dsUser);
            $this->assertNull($result);
            $this->assertDatabaseMissing($regularVisit->getTable(), [$regularVisit->getKeyName() => $regularVisit->getKey()]);
            $this->assertDatabaseMissing($regularVisit->getTable(), ['visit_timestamp' => $regularVisit->visit_timestamp]);
        } finally {
            DB::rollback();
        }
    }
}
