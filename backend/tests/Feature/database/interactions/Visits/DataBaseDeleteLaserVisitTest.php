<?php

namespace Tests\Feature\database\interactions\Visits;

use App\Models\Visit\LaserVisit;
use Database\Interactions\Visits\Deletion\DataBaseDeleteLaserVisit;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * @covers \Database\Interactions\Visits\Deletion\DataBaseDeleteLaserVisit
 */
class DataBaseDeleteLaserVisitTest extends TestCase
{
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

            $laserVisit = LaserVisit::query()->firstOrFail();

            $result = (new DataBaseDeleteLaserVisit)->deleteLaserVisit($laserVisit);

            $this->assertNull($result);
            $this->assertDatabaseMissing($laserVisit->getTable(), [$laserVisit->getKeyName() => $laserVisit->getKey()]);

            DB::rollBack();

            $this->assertDatabaseHas($laserVisit->getTable(), [$laserVisit->getKeyName() => $laserVisit->getKey()]);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
