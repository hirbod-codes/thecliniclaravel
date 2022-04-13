<?php

namespace Tests\Unit\database\interactions\Visits;

use App\Models\Order\LaserOrder;
use App\Models\Visit\LaserVisit;
use Database\Interactions\Visits\DataBaseDeleteLaserVisit;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;

class DataBaseDeleteLaserVisitTest extends TestCase
{
    use GetAuthenticatables;

    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testDeleteLaserVisit(): void
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
                     *  @var LaserOrder $laserOrder
                     *  @var LaserVisit $laserVisit
                     *  @var LaserVisit[] $laserVisits
                     */
                    if (($laserOrder = $order->laserOrder) !== null && count($laserVisits = $laserOrder->laserVisits) !== 0) {
                        $dsLaserOrder = $laserOrder->getDSLaserOrder();
                        $laserVisit = $laserVisits[0];
                        $dsLaserVisit = $laserVisits[0]->getDSLaserVisit();
                        break 2;
                    }
                }

                $safety++;
            }
            if (!isset($dsLaserVisit)) {
                throw new ModelNotFoundException('', 404);
            }

            $result = (new DataBaseDeleteLaserVisit)->deleteLaserVisit($dsLaserVisit, $dsUser);
            $this->assertNull($result);
            $this->assertDatabaseMissing($laserVisit->getTable(), [$laserVisit->getKeyName() => $laserVisit->getKey()]);
            $this->assertDatabaseMissing($laserVisit->getTable(), ['visit_timestamp' => $laserVisit->visit_timestamp]);
        } finally {
            DB::rollback();
        }
    }
}
