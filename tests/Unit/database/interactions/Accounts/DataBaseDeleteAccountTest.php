<?php

namespace Tests\Unit\database\interactions\Accounts;

use App\Models\User;
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

    public function testDeleteAccount()
    {
        foreach ($this->getAuthenticatables() as $ruleName => $authenticatable) {
            try {
                DB::beginTransaction();

                $dsAuthenticatable = $authenticatable->getDatastructure();

                (new DataBaseDeleteAccount)->deleteAccount($dsAuthenticatable);

                $theModelClassFullName = $this->resolveRuleModelFullName($ruleName);

                $this->assertDatabaseMissing((new $theModelClassFullName)->getTable(), [
                    (new $theModelClassFullName)->getKeyName() => $dsAuthenticatable->getId()
                ]);

                $this->assertDatabaseMissing((new User)->getTable(), [
                    'username' => $dsAuthenticatable->getUsername()
                ]);

                DB::rollback();
            } catch (\Throwable $th) {
                DB::rollback();
                throw $th;
            }
        }
    }
}
