<?php

namespace Tests\Unit\database\interaction\Accounts;

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
        try {
            DB::beginTransaction();

            $input = ['firstname' => $this->faker->firstName(), 'lastname' => $this->faker->lastName()];
            $ruleName = 'patient';

            $authenticatable = $this->getAuthenticatable($ruleName);

            $theDataStructureClassFullName = $this->resolveRuleDataStructureFullName($ruleName);

            $account = (new DataBaseUpdateAccount)->massUpdateAccount($input, $authenticatable->getDataStructure());

            $this->assertInstanceOf($theDataStructureClassFullName, $account);
            $this->assertEquals($input['firstname'], $account->getFirstname());
            $this->assertEquals($input['lastname'], $account->getLastname());

            try {
                $anotherAuthenticatable = $this->getAuthenticatable($ruleName);
                $input = ['firstname' => $anotherAuthenticatable->getDataStructure()->getFirstname(), 'lastname' => $anotherAuthenticatable->getDataStructure()->getLastname()];

                $account = (new DataBaseUpdateAccount)->massUpdateAccount($input, $authenticatable->getDataStructure());
                throw new \RuntimeException('Failure!!!');
            } catch (\Throwable $th) {
                if ($th->getMessage() !== 'A user with same first name and last name already exists.') {
                    throw $th;
                }
            }
        } finally {
            DB::rollBack();
        }
    }

    public function testUpdateAccount()
    {
        try {
            DB::beginTransaction();

            $ruleName = 'patient';

            $anotherAuthenticatable = $this->getAuthenticatable($ruleName);

            $authenticatable = $this->getAuthenticatable($ruleName);
            $attribute = 'lastname';
            $newValue = $anotherAuthenticatable->lastname;

            $account = (new DataBaseUpdateAccount)->updateAccount($attribute, $newValue, $authenticatable->getDataStructure());

            $theDataStructureClassFullName = $this->resolveRuleDataStructureFullName($ruleName);
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
        } finally {
            DB::rollBack();
        }
    }
}
