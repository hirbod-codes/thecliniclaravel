<?php

namespace Tests\Unit\database\interactions\Accounts;

use App\Models\Auth\User;
use App\Models\User as ModelsUser;
use Database\Interactions\Accounts\DataBaseRetrieveAccounts;
use Faker\Factory;
use Faker\Generator;
use Tests\TestCase;

/**
 * @covers \Database\Interactions\Accounts\DataBaseRetrieveAccounts
 */
class DataBaseRetrieveAccountsTest extends TestCase
{
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

        $accounts = (new DataBaseRetrieveAccounts)->getAccounts($count, $ruleName, null);

        $this->assertCount(5, $accounts);
        $this->assertContainsOnlyInstancesOf(User::class, $accounts);
        foreach ($accounts as $account) {
            $this->assertEquals('patient', $account->role->roleName->name);
        }

        $count = 3;
        $ruleName = 'admin';

        $accounts = (new DataBaseRetrieveAccounts)->getAccounts($count, $ruleName, 5);

        $this->assertCount(3, $accounts);
        $this->assertContainsOnlyInstancesOf(User::class, $accounts);
        $this->assertEquals(4, $accounts[0]->getKey());
        foreach ($accounts as $account) {
            $this->assertEquals('admin', $account->role->roleName->name);
        }
    }

    public function testGetAccount()
    {
        $account = (new DataBaseRetrieveAccounts)->getAccount($username = ModelsUser::query()->firstOrFail()->username);

        $this->assertInstanceOf(ModelsUser::class, $account);
        $this->assertEquals($username, $account->username);
    }
}
