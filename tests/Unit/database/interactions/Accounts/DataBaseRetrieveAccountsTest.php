<?php

namespace Tests\Unit\database\interactions\Accounts;

use Database\Interactions\Accounts\DataBaseRetrieveAccounts;
use Database\Traits\ResolveUserModel;
use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;

class DataBaseRetrieveAccountsTest extends TestCase
{
    use ResolveUserModel, GetAuthenticatables;

    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testGetAccounts()
    {
        $count = 5;

        $ruleName = 'patient';

        $theModelClassFullName = $this->resolveRuleModelFullName($ruleName);
        $theModelClassPrimeryKey = (new $theModelClassFullName)->getKeyName();
        $maxId = $theModelClassFullName::orderBy($theModelClassPrimeryKey, 'desc')->first()->{$theModelClassPrimeryKey};

        $theDataStructureClassFullName = $this->resolveRuleDataStructureFullName($ruleName);

        $lastAccountId = $this->faker->randomElement([
            null,
            $this->faker->numberBetween($count + 1, $maxId)
        ]);

        $accounts = (new DataBaseRetrieveAccounts)->getAccounts($count, $ruleName, $lastAccountId);

        $this->assertIsArray($accounts);
        $this->assertCount($count, $accounts);

        for ($i = 0; $i < $count; $i++) {
            $this->assertInstanceOf($theDataStructureClassFullName, $accounts[$i]);
            $this->assertEquals(($lastAccountId ? $lastAccountId - 1 : $maxId) - $i, $accounts[$i]->getId());
        }
    }

    public function testGetAccount()
    {
        $ruleName = 'patient';

        $authenticatable = $this->getAuthenticatable($ruleName);

        $theModelClassFullName = $this->resolveRuleModelFullName($ruleName);
        $theModelClassPrimeryKey = (new $theModelClassFullName)->getKeyName();

        $theDataStructureClassFullName = $this->resolveRuleDataStructureFullName($ruleName);

        $account = (new DataBaseRetrieveAccounts)->getAccount($authenticatable->{$theModelClassPrimeryKey}, $ruleName);

        $this->assertInstanceOf($theDataStructureClassFullName, $account);
        $this->assertEquals($authenticatable->getDataStructure()->getUsername(), $account->getUsername());
    }
}
