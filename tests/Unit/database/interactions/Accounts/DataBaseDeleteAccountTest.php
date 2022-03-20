<?php

namespace Tests\Unit\database\interactions\Accounts;

use Database\Interactions\Accounts\DataBaseDeleteAccount;
use Database\Traits\ResolveUserModel;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;

class DataBaseDeleteAccountTest extends TestCase
{
    use ResolveUserModel, GetAuthenticatables;

    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testFeleteAccount()
    {
        try {
            DB::beginTransaction();

            foreach ($this->getAuthenticatables() as $ruleName => $authenticatable) {
                (new DataBaseDeleteAccount)->deleteAccount($authenticatable->getDatastructure());

                $theModelClassFullName = $this->resolveRuleModelFullName($ruleName);

                $this->assertDatabaseMissing((new $theModelClassFullName)->getTable(), [
                    (new $theModelClassFullName)->getKeyName() => $authenticatable->{(new $theModelClassFullName)->getKeyName()}
                ]);
            }
        } finally {
            DB::rollback();
        }
    }
}
