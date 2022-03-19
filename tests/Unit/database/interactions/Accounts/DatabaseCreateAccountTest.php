<?php

namespace Tests\Unit\database\interaction\Accounts;

use Database\Interactions\Accounts\DataBaseCreateAccount;
use Database\Seeders\DatabaseUsersSeeder;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use TheClinicDataStructures\DataStructures\User\DSUser;

class DatabaseCreateAccountTest extends TestCase
{
    use GetAuthenticatables;

    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testCreateAccount()
    {
        try {
            DB::beginTransaction();

            foreach ($this->getAuthenticatables() as $ruleName => $authenticatable) {
                $input = [
                    'firstname' => $this->faker->firstName(),
                    'lastname' => $this->faker->lastName(),
                    'rule' => $ruleName
                ];

                /** @var DatabaseUsersSeeder|MockInterface $databaseUsersSeeder */
                $databaseUsersSeeder = Mockery::mock(DatabaseUsersSeeder::class);
                $databaseUsersSeeder->shouldReceive('create' . ucfirst($ruleName))
                    ->with(1, ['firstname' => $input['firstname'], 'lastname' => $input['lastname']])
                    ->andReturn([$authenticatable]);

                $dsUser = (new DataBaseCreateAccount($databaseUsersSeeder))->createAccount($input);

                $this->assertInstanceOf(DSUser::class, $dsUser);
                $this->assertEquals($authenticatable->getDataStructure(), $dsUser);
            }
        } finally {
            DB::rollback();
        }
    }
}
