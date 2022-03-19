<?php

namespace Tests\Unit\app\Http\Controller;

use App\Http\Controllers\AccountsController;
use TheClinicUseCases\Accounts\Authentication;
use TheClinicUseCases\Privileges\PrivilegesManagement;
use App\Auth\CheckAuthentication;
use App\Models\Auth\User as AuthUser;
use App\Models\Privilege;
use TheClinicUseCases\Accounts\AccountsManagement;
use Database\Interactions\Accounts\DataBaseCreateAccount;
use Database\Interactions\Accounts\DataBaseDeleteAccount;
use Database\Interactions\Accounts\DataBaseRetrieveAccounts;
use Database\Interactions\Accounts\DataBaseUpdateAccount;
use Database\Traits\ResolveUserModel;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use Mockery\MockInterface;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseCreateAccount;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseDeleteAccount;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseRetrieveAccounts;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseUpdateAccount;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Exceptions\Accounts\AdminTemptsToDeleteAdminException;
use TheClinicUseCases\Exceptions\Accounts\AdminTemptsToUpdateAdminException;

class AccountsControllerTest extends TestCase
{
    use ResolveUserModel, GetAuthenticatables;

    private Generator $faker;

    private string $ruleName;

    private AuthUser $user;

    private DSUser $dsUser;

    /**
     * @var array<string, \App\Models\Auth\User> ['ruleName' => \App\Models\Auth\User, ...]
     */
    private array $users;

    private Authentication|MockInterface $authentication;

    private PrivilegesManagement|MockInterface $privilegesManagement;

    private CheckAuthentication|MockInterface $checkAuthentication;

    private AccountsManagement|MockInterface $accountsManagement;

    private DataBaseRetrieveAccounts|MockInterface $dataBaseRetrieveAccounts;

    private DataBaseCreateAccount|MockInterface $dataBaseCreateAccount;

    private DataBaseUpdateAccount|MockInterface $dataBaseUpdateAccount;

    private DataBaseDeleteAccount|MockInterface $dataBaseDeleteAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        /** @var \TheClinicUseCases\Accounts\Authentication|\Mockery\MockInterface $authentication */
        $this->authentication = Mockery::mock(Authentication::class);

        /** @var \TheClinicUseCases\Privileges\PrivilegesManagement|\Mockery\MockInterface $privilegesManagement */
        $this->privilegesManagement = Mockery::mock(PrivilegesManagement::class);

        /** @var \TheClinicUseCases\Accounts\Interfaces\IDataBaseCreateAccount|\Mockery\MockInterface $dataBaseRetrieveAccounts */
        $this->dataBaseRetrieveAccounts = Mockery::mock(IDataBaseRetrieveAccounts::class);

        /** @var \TheClinicUseCases\Accounts\Interfaces\IDataBaseDeleteAccount|\Mockery\MockInterface $dataBaseCreateAccount */
        $this->dataBaseCreateAccount = Mockery::mock(IDataBaseCreateAccount::class);

        /** @var \TheClinicUseCases\Accounts\Interfaces\IDataBaseRetrieveAccounts|\Mockery\MockInterface $dataBaseUpdateAccount */
        $this->dataBaseUpdateAccount = Mockery::mock(IDataBaseUpdateAccount::class);

        /** @var \TheClinicUseCases\Accounts\Interfaces\IDataBaseUpdateAccount|\Mockery\MockInterface $dataBaseDeleteAccount */
        $this->dataBaseDeleteAccount = Mockery::mock(IDataBaseDeleteAccount::class);

        /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
        $this->accountsManagement = Mockery::mock(AccountsManagement::class);
    }

    private function instantiate(): AccountsController
    {
        return new AccountsController(
            $this->authentication,
            $this->privilegesManagement,
            $this->checkAuthentication,
            $this->accountsManagement,
            $this->dataBaseRetrieveAccounts,
            $this->dataBaseCreateAccount,
            $this->dataBaseUpdateAccount,
            $this->dataBaseDeleteAccount,
        );
    }

    public function testsRun()
    {
        $this->users = $this->getAuthenticatables();
        $methods = [
            'testIndex',
            'testCreate',
            'testStore',
            'testShow',
            'testShowWithSameId',
            'testEdit',
            'testUpdate',
            'testUpdateWithSameId',
            'testDestroy',
            'testDestroyWithSameId'
        ];

        foreach ($methods as $method) {
            foreach ($this->users as $ruleName => $user) {
                $this->ruleName = $ruleName;

                $this->user = $user;

                $this->dsUser = $this->user->getDataStructure();

                /** @var \App\Http\Controllers\CheckAuthentication|\Mockery\MockInterface $checkAuthentication */
                $this->checkAuthentication = Mockery::mock(CheckAuthentication::class);
                $this->checkAuthentication->shouldReceive("getAuthenticatedDSUser")->andReturn($this->dsUser);
                $this->checkAuthentication->shouldReceive("getAuthenticated")->andReturn($this->user);

                $this->{$method}();
            }
        }
    }

    private function testIndex(): void
    {
        $ruleName = 'admin';

        $count = $this->faker->numberBetween(1, 30);
        $lastAccountId = $this->faker->numberBetween(1, 1000);

        $newDSUser = $this->getAuthenticatable($this->ruleName)->getDataStructure();

        $this->accountsManagement->shouldReceive("getAccounts")
            ->once()
            ->with($lastAccountId, $count, $ruleName, $this->dsUser, $this->dataBaseRetrieveAccounts)
            ->andReturn([$newDSUser]);

        $accountsController = $this->instantiate();

        $jsonResponse = $accountsController->index($ruleName, $lastAccountId, $count);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);

        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(1, $jsonResponse->original);
        $this->assertEquals($newDSUser->toArray(), $jsonResponse->original[0]);
    }

    private function testCreate(): void
    {
        $accountsController = $this->instantiate();

        $jsonResponse = $accountsController->create();
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count(Privilege::all()), $jsonResponse->original);

        foreach ($jsonResponse->original as $privilegeName => $privilege) {
            $this->assertIsString($privilegeName);
            $this->assertNotNull($privilege);
        }
    }

    private function testStore(): void
    {
        $input = [];
        $authenticatables = $this->getAuthenticatables();

        foreach ($authenticatables as $ruleName => $authenticatable) {
            /** @var \Illuminate\Http\Request|\Mockery\MockInterface $request */
            $request = Mockery::mock(Request::class);
            $request->shouldReceive('all')->andReturn($input);
            $request->rule = $ruleName;

            $dsNewUser = $authenticatable->getDataStructure();
            $dsNewUserArray = $dsNewUser->toArray();

            /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
            $this->accountsManagement = Mockery::mock(AccountsManagement::class);
            $this->accountsManagement
                ->shouldReceive("createAccount")
                ->with($input, $this->dsUser, $this->dataBaseCreateAccount)
                ->andReturn($dsNewUser);

            $accountsController = $this->instantiate();

            $jsonResponse = $accountsController->store($request);
            $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
            $this->assertIsArray($jsonResponse->original);
            $this->assertCount(count($dsNewUserArray), $jsonResponse->original);

            foreach ($dsNewUserArray as $key => $value) {
                $this->assertArrayHasKey($key, $jsonResponse->original);
                $this->assertEquals($jsonResponse->original[$key], $value);
            }
        }
    }

    private function testShow(): void
    {
        $authenticatables = $this->getAuthenticatables();

        foreach ($authenticatables as $ruleName => $authenticatable) {
            $dsNewUser = $authenticatable->getDataStructure();
            $dsNewUserArray = $dsNewUser->toArray();

            $anotherId = $this->dsUser->getId();
            while ($anotherId === $this->dsUser->getId()) {
                $anotherId = $this->faker->numberBetween(1, 1000);
            }

            /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
            $this->accountsManagement = Mockery::mock(AccountsManagement::class);
            $this->accountsManagement
                ->shouldReceive("getAccount")
                ->with($anotherId, $ruleName, $this->dsUser, $this->dataBaseRetrieveAccounts)
                ->andReturn($dsNewUser);

            $accountsController = $this->instantiate();

            $jsonResponse = $accountsController->show($anotherId, $ruleName);
            $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
            $this->assertIsArray($jsonResponse->original);
            $this->assertCount(count($dsNewUserArray), $jsonResponse->original);

            foreach ($dsNewUserArray as $key => $value) {
                $this->assertArrayHasKey($key, $jsonResponse->original);
                $this->assertEquals($jsonResponse->original[$key], $value);
            }
        }
    }

    private function testShowWithSameId(): void
    {
        $authenticatables = $this->getAuthenticatables();

        foreach ($authenticatables as $ruleName => $authenticatable) {
            $dsNewUser = $authenticatable->getDataStructure();
            $dsNewUserArray = $dsNewUser->toArray();

            /** @var \App\Http\Controllers\CheckAuthentication|\Mockery\MockInterface $checkAuthentication */
            $this->checkAuthentication = Mockery::mock(CheckAuthentication::class);
            $this->checkAuthentication->shouldReceive("getAuthenticatedDSUser")->andReturn($dsNewUser);

            /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
            $this->accountsManagement = Mockery::mock(AccountsManagement::class);
            $this->accountsManagement
                ->shouldReceive("getSelfAccount")
                ->with($ruleName, $dsNewUser, $this->dataBaseRetrieveAccounts)
                ->andReturn($dsNewUser);

            $accountsController = $this->instantiate();

            $jsonResponse = $accountsController->show($dsNewUser->getId(), $ruleName);
            $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
            $this->assertIsArray($jsonResponse->original);
            $this->assertCount(count($dsNewUserArray), $jsonResponse->original);

            foreach ($dsNewUserArray as $key => $value) {
                $this->assertArrayHasKey($key, $jsonResponse->original);
                $this->assertEquals($jsonResponse->original[$key], $value);
            }
        }
    }

    private function testEdit(): void
    {
        $id = $this->faker->numberBetween(1, 1000);

        $accountsController = $this->instantiate();

        $jsonResponse = $accountsController->edit($id);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count(Privilege::all()), $jsonResponse->original);

        foreach ($jsonResponse->original as $privilegeName => $privilege) {
            $this->assertIsString($privilegeName);
            $this->assertNotNull($privilege);
        }
    }

    private function testUpdate(): void
    {
        $input = ['input'];
        $authenticatables = $this->getAuthenticatables(true);

        foreach ($authenticatables as $ruleName => $authenticatable) {
            $dsNewAuthenticatable = $authenticatable->getDataStructure();
            $dsNewUserArray = $dsNewAuthenticatable->toArray();

            $anotherId = $dsNewAuthenticatable->getId();

            /** @var \Illuminate\Http\Request|\Mockery\MockInterface $request */
            $request = Mockery::mock(Request::class);
            $request->shouldReceive('all')->andReturn($input);
            $request->rule = $ruleName;

            /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
            $this->accountsManagement = Mockery::mock(AccountsManagement::class);
            $this->accountsManagement
                ->shouldReceive('updateAccount')
                ->with($input, \Mockery::on(function (DSUser $arg) use ($dsNewAuthenticatable) {
                    if ($arg->getUsername() === $dsNewAuthenticatable->getUsername()) {
                        return true;
                    }
                    return false;
                }), $this->dsUser, \Mockery::type(IDataBaseUpdateAccount::class))
                ->andReturn($anotherDSNewUser = $this->getAuthenticatable($ruleName)->getDataStructure());

            $accountsController = $this->instantiate();

            $jsonResponse = $accountsController->update($request, $anotherId);
            $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
            $this->assertIsArray($jsonResponse->original);
            $this->assertCount(count($anotherDSNewUser->toArray()), $jsonResponse->original);

            foreach ($anotherDSNewUser->toArray() as $key => $value) {
                $this->assertArrayHasKey($key, $jsonResponse->original);
                $this->assertEquals($jsonResponse->original[$key], $value);
            }

            /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
            $this->accountsManagement = Mockery::mock(AccountsManagement::class);
            $this->accountsManagement->shouldReceive('updateAccount')
                ->with($input, \Mockery::on(function (DSUser $arg) use ($dsNewAuthenticatable) {
                    if ($arg->getUsername() === $dsNewAuthenticatable->getUsername()) {
                        return true;
                    }
                    return false;
                }), $this->dsUser, \Mockery::type(IDataBaseUpdateAccount::class))
                ->andThrow(AdminTemptsToUpdateAdminException::class, (new AdminTemptsToUpdateAdminException)->getMessage(), (new AdminTemptsToUpdateAdminException)->getCode());

            $accountsController = $this->instantiate();

            $response = $accountsController->update($request, $anotherId);
            $this->assertInstanceOf(Response::class, $response);
            $this->assertIsString($response->original);
            $this->assertEquals('An admin user can not update another admin user.', $response->original);
            $this->assertEquals(403, $response->status());
        }
    }

    private function testUpdateWithSameId(): void
    {
        $input = ['input'];
        $anotherId = $this->dsUser->getId();

        /** @var \Illuminate\Http\Request|\Mockery\MockInterface $request */
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('all')->andReturn($input);
        $request->rule = $this->ruleName;

        /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
        $this->accountsManagement = Mockery::mock(AccountsManagement::class);
        $this->accountsManagement
            ->shouldReceive('updateSelfAccount')
            ->with(0, 'input', $this->dsUser, \Mockery::type(IDataBaseUpdateAccount::class))
            ->andReturn($anotherDSNewUser = $this->getAuthenticatable($this->ruleName)->getDataStructure());

        $accountsController = $this->instantiate();

        $jsonResponse = $accountsController->update($request, $anotherId);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($anotherDSNewUser->toArray()), $jsonResponse->original);

        foreach ($anotherDSNewUser->toArray() as $key => $value) {
            $this->assertArrayHasKey($key, $jsonResponse->original);
            $this->assertEquals($jsonResponse->original[$key], $value);
        }

        /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
        $this->accountsManagement = Mockery::mock(AccountsManagement::class);
        $this->accountsManagement->shouldReceive('updateSelfAccount')
            ->with(0, 'input', $this->dsUser, \Mockery::type(IDataBaseUpdateAccount::class))
            ->andThrow(AdminTemptsToUpdateAdminException::class, (new AdminTemptsToUpdateAdminException)->getMessage(), (new AdminTemptsToUpdateAdminException)->getCode());

        $accountsController = $this->instantiate();

        $response = $accountsController->update($request, $anotherId);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertIsString($response->original);
        $this->assertEquals('An admin user can not update another admin user.', $response->original);
        $this->assertEquals(403, $response->status());
    }

    private function testDestroy(): void
    {
        $authenticatables = $this->getAuthenticatables(true);

        foreach ($authenticatables as $ruleName => $authenticatable) {
            $dsNewAuthenticatable = $authenticatable->getDataStructure();
            $dsNewUserArray = $dsNewAuthenticatable->toArray();

            $anotherId = $dsNewAuthenticatable->getId();

            /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
            $this->accountsManagement = Mockery::mock(AccountsManagement::class);
            $this->accountsManagement
                ->shouldReceive('deleteAccount')
                ->with(\Mockery::on(function (DSUser $arg) use ($dsNewAuthenticatable) {
                    if ($arg->getUsername() === $dsNewAuthenticatable->getUsername()) {
                        return true;
                    }
                    return false;
                }), $this->dsUser, \Mockery::type(IDataBaseDeleteAccount::class));

            $accountsController = $this->instantiate();

            $response = $accountsController->destroy($anotherId, $ruleName);
            $this->assertInstanceOf(Response::class, $response);
            $this->assertIsString($response->original);
            $this->assertEquals('The user successfuly deleted.', $response->original);

            /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
            $this->accountsManagement = Mockery::mock(AccountsManagement::class);
            $this->accountsManagement->shouldReceive('deleteAccount')
                ->with(\Mockery::on(function (DSUser $arg) use ($dsNewAuthenticatable) {
                    if ($arg->getUsername() === $dsNewAuthenticatable->getUsername()) {
                        return true;
                    }
                    return false;
                }), $this->dsUser, \Mockery::type(IDataBaseDeleteAccount::class))
                ->andThrow(AdminTemptsToDeleteAdminException::class, (new AdminTemptsToDeleteAdminException)->getMessage(), (new AdminTemptsToDeleteAdminException)->getCode());

            $accountsController = $this->instantiate();

            $response = $accountsController->destroy($anotherId, $ruleName);
            $this->assertInstanceOf(Response::class, $response);
            $this->assertIsString($response->original);
            $this->assertEquals('An admin user can not delete another admin user.', $response->original);
            $this->assertEquals(403, $response->status());
        }
    }

    private function testDestroyWithSameId(): void
    {
        $anotherId = $this->dsUser->getId();

        /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
        $this->accountsManagement = Mockery::mock(AccountsManagement::class);
        $this->accountsManagement
            ->shouldReceive('deleteSelfAccount')
            ->with($this->dsUser, \Mockery::type(IDataBaseDeleteAccount::class));

        $accountsController = $this->instantiate();

        $response = $accountsController->destroy($anotherId, $this->ruleName);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertIsString($response->original);
        $this->assertEquals('The user successfuly deleted.', $response->original);

        /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
        $this->accountsManagement = Mockery::mock(AccountsManagement::class);
        $this->accountsManagement->shouldReceive('deleteSelfAccount')
            ->with($this->dsUser, \Mockery::type(IDataBaseDeleteAccount::class))
            ->andThrow(AdminTemptsToDeleteAdminException::class, (new AdminTemptsToDeleteAdminException)->getMessage(), (new AdminTemptsToDeleteAdminException)->getCode());

        $accountsController = $this->instantiate();

        $response = $accountsController->destroy($anotherId, $this->ruleName);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertIsString($response->original);
        $this->assertEquals('An admin user can not delete another admin user.', $response->original);
        $this->assertEquals(403, $response->status());
    }
}
