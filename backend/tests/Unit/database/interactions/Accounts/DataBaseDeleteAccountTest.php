<?php

namespace Tests\Unit\database\interactions\Accounts;

use App\Models\User;
use Database\Interactions\Accounts\DataBaseDeleteAccount;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * @covers \Database\Interactions\Accounts\DataBaseDeleteAccount
 */
class DataBaseDeleteAccountTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testDeleteAccount()
    {
        try {
            DB::beginTransaction();

            (new DataBaseDeleteAccount)->deleteAccount($user = User::query()->firstOrFail());

            $this->assertDatabaseMissing($user->getTable(), [$user->getKeyName() => $user->getKey()]);

            DB::rollback();

            $this->assertDatabaseHas($user->getTable(), [$user->getKeyName() => $user->getKey()]);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
