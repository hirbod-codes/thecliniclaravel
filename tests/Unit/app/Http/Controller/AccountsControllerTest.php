<?php

namespace Tests\Unit\app\Http\Controller;

use App\Http\Controllers\AccountsController;
use TheClinicUseCases\Accounts\Authentication;
use TheClinicUseCases\Privileges\PrivilegesManagement;
use App\Auth\CheckAuthentication;
use App\Http\Requests\Accounts\IndexAccountsRequest;
use App\Http\Requests\Accounts\StoreAccountRequest;
use App\Http\Requests\VerifyPhonenumberRequest;
use App\Models\Auth\User as AuthUser;
use App\Notifications\SendPhonenumberVerificationCode;
use TheClinicUseCases\Accounts\AccountsManagement;
use Database\Interactions\Accounts\DataBaseCreateAccount;
use Database\Interactions\Accounts\DataBaseDeleteAccount;
use Database\Interactions\Accounts\DataBaseRetrieveAccounts;
use Database\Interactions\Accounts\DataBaseUpdateAccount;
use Database\Traits\ResolveUserModel;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
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
        $methods = [];
        /** @var \ReflectionMethod $method */
        foreach ((new \ReflectionClass(static::class))->getMethods() as $method) {
            if ($method->class !== static::class) {
                continue;
            }
            $methods[] = $method->name;
        }

        $this->users = $this->getAuthenticatables();

        foreach ($methods as $method) {
            if (!Str::startsWith($method, 'test') || $method === __FUNCTION__) {
                continue;
            }

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

        /** @var IndexAccountsRequest|MockInterface $request */
        $request = Mockery::mock(IndexAccountsRequest::class);
        $request
            ->shouldReceive('safe->all')
            ->andReturn([
                'roleName' => $this->ruleName,
                'lastAccountId' => $lastAccountId,
                'count' => $count
            ])
            //
        ;

        $accountsController = $this->instantiate();

        $jsonResponse = $accountsController->index($request);
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);

        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(1, $jsonResponse->original);
        $this->assertEquals($newDSUser->toArray(), $jsonResponse->original[0]);
    }

    private function testVerifyPhonenumber(): void
    {
        /** @var Session|MockInterface $session */
        $session = Mockery::mock(Session::class);
        $session
            ->shouldReceive('put')
            ->with(
                'verificationCode',
                Mockery::on(function (int $value) {
                    return $value >= 100000 || $value >= 999999;
                })
            )
            //
        ;
        $session
            ->shouldReceive('put')
            ->with(
                'phonenumber',
                Mockery::on(function (string $value) {
                    return true;
                })
            )
            //
        ;

        /** @var VerifyPhonenumberRequest|MockInterface $request */
        $request = Mockery::mock(VerifyPhonenumberRequest::class);
        $request
            ->shouldReceive('safe->all')
            ->andReturn([
                'phonenumber' => $phonenumber = $this->faker->phoneNumber(),
            ])
            //
        ;
        $request
            ->shouldReceive('session')
            ->andReturn($session)
            //
        ;

        Notification::fake();

        $response = $this->instantiate()->verifyPhonenumber($request);

        $this->assertInstanceOf(Response::class, $response);
        Notification::assertSentTo(
            [(new AnonymousNotifiable)->route('sms', $phonenumber)],
            SendPhonenumberVerificationCode::class
        );
    }

    private function testStore(): void
    {
        $authenticatables = $this->getAuthenticatables();

        foreach ($authenticatables as $ruleName => $authenticatable) {
            $requestInput = [
                'code' => $this->faker->numberBetween(100000, 999999),
                'password_confirmation' => $this->faker->lexify(),
            ];

            /** @var StoreAccountRequest|MockInterface $request */
            $request = Mockery::mock(StoreAccountRequest::class);
            $request->shouldReceive('safe->all')->andreturn($requestInput);

            $dsNewUser = $authenticatable->getDataStructure();
            $dsNewUserArray = $dsNewUser->toArray();

            /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
            $this->accountsManagement = Mockery::mock(AccountsManagement::class);
            $this->accountsManagement
                ->shouldReceive("createAccount")
                ->with([], $this->dsUser, $this->dataBaseCreateAccount)
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
            $this->assertEquals(200, $jsonResponse->getStatusCode());
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
