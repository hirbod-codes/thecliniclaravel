<?php

namespace Tests\Unit\database\interactions\Accounts;

use Database\Interactions\Accounts\DataBaseUpdateAccount;
use Database\Traits\ResolveUserModel;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;

class DataBaseUpdateAccountTest extends TestCase
{
    use ResolveUserModel, GetAuthenticatables;

    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testMassUpdateAccount()
    {
        // $this->markTestIncomplete();
        foreach ($this->getAuthenticatables() as $ruleName => $authenticatable) {
            try {
                DB::beginTransaction();

                $input = [
                    'firstname' => $this->faker->unique()->firstName(),
                    'lastname' => $this->faker->unique()->lastName()
                ];

                $theDataStructureClassFullName = $this->resolveRuleDataStructureFullName($ruleName);

                $account = (new DataBaseUpdateAccount)->massUpdateAccount($input, $authenticatable->getDataStructure());

                $this->assertInstanceOf($theDataStructureClassFullName, $account);
                $this->assertEquals($input['firstname'], $account->getFirstname());
                $this->assertEquals($input['lastname'], $account->getLastname());

                try {
                    $anotherAuthenticatable = $this->getAuthenticatable($ruleName);
                    $input = [
                        'firstname' => $anotherAuthenticatable->getDataStructure()->getFirstname(),
                        'lastname' => $anotherAuthenticatable->getDataStructure()->getLastname()
                    ];

                    $account = (new DataBaseUpdateAccount)->massUpdateAccount($input, $authenticatable->getDataStructure());
                    throw new \RuntimeException('Failure!!!');
                } catch (\Throwable $th) {
                    if ($th->getMessage() !== 'A user with same first name and last name already exists.') {
                        throw $th;
                    }
                }

                DB::rollBack();
            } catch (\Throwable $th) {
                DB::rollBack();
                throw $th;
            }
            break;
        }
    }

    public function testUpdateAccount()
    {
        try {
            foreach ($this->getAuthenticatables() as $roleName => $authenticatable) {
                DB::beginTransaction();

                $anotherAuthenticatable = $this->getAuthenticatable($roleName)->user()->first();

                $attribute = 'lastname';
                $newValue = $anotherAuthenticatable->lastname;

                $account = (new DataBaseUpdateAccount)->updateAccount($attribute, $newValue, $authenticatable->getDataStructure());

                $theDataStructureClassFullName = $this->resolveRuleDataStructureFullName($roleName);
                $this->assertInstanceOf($theDataStructureClassFullName, $account);
                $this->assertEquals($newValue, $account->getLastname());

                $attribute = 'firstname';
                $newValue = $anotherAuthenticatable->firstname;

                try {
                    (new DataBaseUpdateAccount)->updateAccount($attribute, $newValue, $authenticatable->getDataStructure());
                    throw new \RuntimeException('Failure!!!');
                } catch (\Throwable $th) {
                    if ($th->getMessage() !== 'A user with same first name and last name already exists.') {
                        throw $th;
                    }
                }

                DB::rollback();
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
