<?php

namespace Tests\Feature\database\interactions\Visits;

use App\Models\Visit\RegularVisit;
use Database\Interactions\Visits\Deletion\DataBaseDeleteRegularVisit;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * @covers \Database\Interactions\Visits\Deletion\DataBaseDeleteRegularVisit
 */
class DataBaseDeleteRegularVisitTest extends TestCase
{
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

            $regularVisit = RegularVisit::query()->firstOrFail();

            $result = (new DataBaseDeleteRegularVisit)->deleteRegularVisit($regularVisit);

            $this->assertNull($result);
            $this->assertDatabaseMissing($regularVisit->getTable(), [$regularVisit->getKeyName() => $regularVisit->getKey()]);

            DB::rollBack();

            $this->assertDatabaseHas($regularVisit->getTable(), [$regularVisit->getKeyName() => $regularVisit->getKey()]);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
