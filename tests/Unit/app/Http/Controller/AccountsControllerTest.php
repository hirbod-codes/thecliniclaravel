<?php

namespace Tests\Unit\app\Http\Controller;

use App\Http\Controllers\AccountsController;
use TheClinicUseCases\Accounts\Authentication;
use TheClinicUseCases\Privileges\PrivilegesManagement;
use App\Auth\CheckAuthentication;
use App\Models\Auth\User as AuthUser;
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

    public function testRun()
    {
        $methods = [
            'testIndex',
            // 'testCreate',
            'testStore',
            'testShow',
            'testShowSelf',
            // 'testEdit',
            'testUpdate',
            'testUpdateSelf',
            'testDestroy',
            'testDestroySelf'
        ];

        $this->users = $this->getAuthenticatables();

        foreach ($methods as $method) {
            // because of perfomance i chose a random user from $this->users.
            $this->ruleName = $this->faker->randomElement(array_keys($this->users));

            $this->user = $this->users[$this->ruleName];

            $this->dsUser = $this->user->getDataStructure();

            /** @var \App\Http\Controllers\CheckAuthentication|\Mockery\MockInterface $checkAuthentication */
            $this->checkAuthentication = Mockery::mock(CheckAuthentication::class);
            $this->checkAuthentication->shouldReceive("getAuthenticatedDSUser")->andReturn($this->dsUser);
            $this->checkAuthentication->shouldReceive("getAuthenticated")->andReturn($this->user);

            $this->{$method}();
        }
    }

    private function testIndex(): void
    {
        $count = $this->faker->numberBetween(1, 30);
        $lastAccountId = $this->faker->numberBetween(1, 1000);

        $newDSUser = $this->getAuthenticatable($this->ruleName)->getDataStructure();

        $this->accountsManagement->shouldReceive("getAccounts")
            ->once()
            ->with($lastAccountId, $count, $this->ruleName, $this->dsUser, $this->dataBaseRetrieveAccounts)
            ->andReturn([$newDSUser]);

        $accountsController = $this->instantiate();

        $jsonResponse = $accountsController->index($this->ruleName, $lastAccountId, $count);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);

        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(1, $jsonResponse->original);
        $this->assertEquals($newDSUser->toArray(), $jsonResponse->original[0]);
    }

    // private function testCreate(): void
    // {
    // $accountsController = $this->instantiate();

    // $jsonResponse = $accountsController->create();
    // $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
    // $this->assertIsArray($jsonResponse->original);
    // $this->assertCount(count(Privilege::all()), $jsonResponse->original);

    // foreach ($jsonResponse->original as $privilegeName => $privilege) {
    //     $this->assertIsString($privilegeName);
    //     $this->assertNotNull($privilege);
    // }
    // }

    private function testStore(): void
    {
        $input = [];
        $authenticatables = $this->getAuthenticatables();

        foreach ($authenticatables as $ruleName => $authenticatable) {
            $code = $this->faker->numberBetween(100000, 999999);
            $phonenumber = $this->faker->phoneNumber();
            $requestInput = [];

            /** @var Session|MockInterface $session */
            $session = Mockery::mock(Session::class);
            $session
                ->shouldReceive('get')
                ->with('verificationCode', 0)
                ->andReturn($code)
                //
            ;
            $session
                ->shouldReceive('get')
                ->with('phonenumber', '')
                ->andReturn($phonenumber)
                //
            ;

            /** @var Request|MockInterface $request */
            $request = Mockery::mock(Request::class);
            $request->phonenumber = $phonenumber;
            $request->code = $code;
            $request->shouldReceive('session')->andreturn($session);
            $request->shouldReceive('all')->andreturn($requestInput);
            $request->shouldReceive('offsetUnset');
            $request->shouldReceive('offsetSet');

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
            $username = $authenticatable->user->username;
            $dsNewUser = $authenticatable->getDataStructure();
            $dsNewUserArray = $dsNewUser->toArray();

            /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
            $this->accountsManagement = Mockery::mock(AccountsManagement::class);
            $this->accountsManagement
                ->shouldReceive("getAccount")
                ->with($username, $this->dsUser, $this->dataBaseRetrieveAccounts)
                ->andReturn($dsNewUser);

            $accountsController = $this->instantiate();

            $jsonResponse = $accountsController->show($username);
            $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
            $this->assertIsArray($jsonResponse->original);
            $this->assertCount(count($dsNewUserArray), $jsonResponse->original);

            foreach ($dsNewUserArray as $key => $value) {
                $this->assertArrayHasKey($key, $jsonResponse->original);
                $this->assertEquals($jsonResponse->original[$key], $value);
            }
        }
    }

    private function testShowSelf(): void
    {
        $dsUserArray = $this->dsUser->toArray();

        /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
        $this->accountsManagement = Mockery::mock(AccountsManagement::class);
        $this->accountsManagement
            ->shouldReceive("getAccount")
            ->with($this->dsUser->getUsername(), $this->dsUser, $this->dataBaseRetrieveAccounts)
            ->andReturn($this->dsUser);

        $accountsController = $this->instantiate();

        $jsonResponse = $accountsController->showSelf();
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($dsUserArray), $jsonResponse->original);

        foreach ($dsUserArray as $key => $value) {
            $this->assertArrayHasKey($key, $jsonResponse->original);
            $this->assertEquals($jsonResponse->original[$key], $value);
        }
    }

    // private function testEdit(): void
    // {
    // $id = $this->faker->numberBetween(1, 1000);

    // $accountsController = $this->instantiate();

    // $jsonResponse = $accountsController->edit($id);
    // $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
    // $this->assertIsArray($jsonResponse->original);
    // $this->assertCount(count(Privilege::all()), $jsonResponse->original);

    // foreach ($jsonResponse->original as $privilegeName => $privilege) {
    //     $this->assertIsString($privilegeName);
    //     $this->assertNotNull($privilege);
    // }
    // }

    private function testUpdate(): void
    {
        $input = ['input'];
        $authenticatables = $this->getAuthenticatables(true);

        foreach ($authenticatables as $ruleName => $authenticatable) {
            $anotherId = $authenticatable->getKey();
            $dsNewAuthenticatable = $authenticatable->getDataStructure();

            /** @var \Illuminate\Http\Request|\Mockery\MockInterface $request */
            $request = Mockery::mock(Request::class);
            $request->shouldReceive('all')->andReturn($input);

            /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
            $this->accountsManagement = Mockery::mock(AccountsManagement::class);
            $this->accountsManagement
                ->shouldReceive('massUpdateAccount')
                ->with(
                    $input,
                    \Mockery::on(function (DSUser $arg) use ($dsNewAuthenticatable) {
                        if ($arg->getUsername() === $dsNewAuthenticatable->getUsername()) {
                            return true;
                        }
                        return false;
                    }),
                    $this->dsUser,
                    \Mockery::type(IDataBaseUpdateAccount::class)
                )
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
        }
    }

    private function testUpdateSelf(): void
    {
        $input = ['input'];
        $anotherId = $this->dsUser->getId();

        /** @var \Illuminate\Http\Request|\Mockery\MockInterface $request */
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('all')->andReturn($input);

        /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
        $this->accountsManagement = Mockery::mock(AccountsManagement::class);
        $this->accountsManagement
            ->shouldReceive('massUpdateAccount')
            ->with(
                $input,
                \Mockery::on(function (DSUser $arg) {
                    if ($arg->getUsername() === $this->dsUser->getUsername()) {
                        return true;
                    }
                    return false;
                }),
                $this->dsUser,
                \Mockery::type(IDataBaseUpdateAccount::class)
            )
            ->andReturn($anotherDSNewUser = $this->getAuthenticatable('admin')->getDataStructure());

        $accountsController = $this->instantiate();

        $jsonResponse = $accountsController->updateSelf($request, $anotherId);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(count($anotherDSNewUser->toArray()), $jsonResponse->original);

        foreach ($anotherDSNewUser->toArray() as $key => $value) {
            $this->assertArrayHasKey($key, $jsonResponse->original);
            $this->assertEquals($jsonResponse->original[$key], $value);
        }
    }

    private function testDestroy(): void
    {
        $authenticatables = $this->getAuthenticatables(true);

        foreach ($authenticatables as $ruleName => $authenticatable) {
            $anotherId = $authenticatable->getKey();
            $dsNewAuthenticatable = $authenticatable->getDataStructure();
            $dsNewUserArray = $dsNewAuthenticatable->toArray();


            /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
            $this->accountsManagement = Mockery::mock(AccountsManagement::class);
            $this->accountsManagement
                ->shouldReceive('deleteAccount')
                ->with(
                    \Mockery::on(function (DSUser $arg) use ($dsNewAuthenticatable) {
                        if ($arg->getUsername() === $dsNewAuthenticatable->getUsername()) {
                            return true;
                        }
                        return false;
                    }),
                    $this->dsUser,
                    \Mockery::type(IDataBaseDeleteAccount::class)
                );

            $accountsController = $this->instantiate();

            $response = $accountsController->destroy($anotherId);
            $this->assertInstanceOf(Response::class, $response);
            $this->assertIsString($response->original);
            $this->assertEquals('The user successfuly deleted.', $response->original);
        }
    }

    private function testDestroySelf(): void
    {
        /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
        $this->accountsManagement = Mockery::mock(AccountsManagement::class);
        $this->accountsManagement
            ->shouldReceive('deleteAccount')
            ->with(
                \Mockery::on(function (DSUser $arg) {
                    if ($arg->getUsername() === $this->dsUser->getUsername()) {
                        return true;
                    }
                    return false;
                }),
                $this->dsUser,
                \Mockery::type(IDataBaseDeleteAccount::class)
            );

        $accountsController = $this->instantiate();

        $response = $accountsController->destroySelf();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertIsString($response->original);
        $this->assertEquals('The user successfuly deleted.', $response->original);
    }
}
