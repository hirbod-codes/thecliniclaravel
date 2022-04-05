<?php

namespace Tests\Unit\database\interactions\Orders\Deletion;

use App\Models\Order\RegularOrder;
use Database\Interactions\Orders\Deletion\DataBaseDeleteRegularOrder;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;

class DataBaseDeleteRegularOrderTest extends TestCase
{
    use GetAuthenticatables;

    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testDeleteRegularOrder(): void
    {
        DB::beginTransaction();

        try {
            $authenticatable = $this->getAuthenticatable('patient');
            $dsAuthenticatable = $authenticatable->getDataStructure();

            foreach ($authenticatable->user->orders as $order) {
                /** @var RegularOrder $regularOrder */
                if (($regularOrder = $order->regularOrder) !== null) {
                    $found = true;
                    break;
                }
            }
            if (!isset($found)) {
                throw new ModelNotFoundException();
            }

            $result = (new DataBaseDeleteRegularOrder)->deleteRegularOrder($regularOrder->getDSRegularOrder(), $dsAuthenticatable);
            $this->assertNull($result);
        } finally {
            DB::rollBack();
        }
    }
}
