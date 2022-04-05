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

        $theDataStructureClassFullName = $this->resolveRuleDataStructureFullName($ruleName);

        $ids = $this->getRandomId('patient');
        for ($i = 0; $i < $count; $i++) {
            array_pop($ids);
        }

        $lastAccountId = $this->faker->randomElement([
            null,
            $this->faker->randomElement($ids)
        ]);

        $accounts = (new DataBaseRetrieveAccounts)->getAccounts($count, $ruleName, $lastAccountId);

        $this->assertIsArray($accounts);
        $this->assertCount($count, $accounts);

        for ($i = 0; $i < $count; $i++) {
            $this->assertInstanceOf($theDataStructureClassFullName, $accounts[$i]);
        }
    }

    public function testGetAccount()
    {
        $ruleName = 'patient';

        $authenticatable = $this->getAuthenticatable($ruleName);

        $account = (new DataBaseRetrieveAccounts)->getAccount($authenticatable->getDataStructure()->getUsername());

        $this->assertInstanceOf(get_class($authenticatable->getDataStructure()), $account);
        $this->assertEquals($authenticatable->getDataStructure()->getUsername(), $account->getUsername());
    }
}
