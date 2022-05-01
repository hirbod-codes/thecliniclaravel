<?php

namespace Tests\Unit\app\Http\Controller;

use App\Http\Controllers\AccountsController;
use TheClinicUseCases\Accounts\Authentication;
use TheClinicUseCases\Privileges\PrivilegesManagement;
use App\Auth\CheckAuthentication;
use App\Http\Requests\Accounts\IndexAccountsRequest;
use App\Http\Requests\Accounts\UpdateAccountRequest;
use App\Http\Requests\Accounts\ApiVerifyPhonenumberVerificationCodeRequest;
use App\Http\Requests\Accounts\SendPhonenumberVerificationCodeRequest;
use App\Http\Requests\Accounts\StoreDoctorAccountRequest;
use App\Http\Requests\Accounts\StoreOperatorAccountRequest;
use App\Http\Requests\Accounts\StorePatientAccountRequest;
use App\Http\Requests\Accounts\StoreSecretaryAccountRequest;
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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Crypt;
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

    private function testSendPhonenumberVerificationCode(): void
    {
        $validatedInput = [
            'phonenumber' => $phonenumber = $this->faker->phoneNumber(),
        ];

        /** @var SendPhonenumberVerificationCodeRequest|MockInterface $request */
        $request = Mockery::mock(SendPhonenumberVerificationCodeRequest::class);
        $request
            ->shouldReceive('safe->all')
            ->andReturn($validatedInput)
            //
        ;

        Notification::fake();

        $response = $this->instantiate()->sendPhonenumberVerificationCode($request);

        $this->assertInstanceOf(JsonResponse::class, $response);

        Notification::assertSentTo(
            [(new AnonymousNotifiable)->route('sms', $phonenumber)],
            SendPhonenumberVerificationCode::class
        );

        $keys = [
            'code_created_at_encrypted',
            'code_encrypted',
            'phonenumber_encrypted',
            'phonenumber_verified_at_encrypted',
        ];
        $this->assertCount(4, $response->original);
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $response->original);
            $this->assertNotEmpty($response->original[$key]);
        }
    }

    private function testVerifyPhonenumberVerificationCode(): void
    {
        $id = $this->faker->numberBetween(100000, 999999);
        $code = strval($this->faker->numberBetween(100000, 999999));
        $validatedInput = [
            'code_created_at_encrypted' => Crypt::encryptString(strval((new \DateTime)->getTimestamp()) . AccountsController::SEPARATOR . strval($id)),
            'code_encrypted' => Crypt::encryptString(strval($code) . AccountsController::SEPARATOR . strval($id)),
            'code' => $code,
        ];

        /** @var ApiVerifyPhonenumberVerificationCodeRequest|MockInterface $request */
        $request = Mockery::mock(ApiVerifyPhonenumberVerificationCodeRequest::class);
        $request
            ->shouldReceive('safe->all')
            ->andReturn($validatedInput)
            //
        ;

        $response = $this->instantiate()->verifyPhonenumberVerificationCode($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(trans_choice('auth.phonenumber_verification_successful', 0), $response->original);
    }

    private function testStoreDoctor(): void
    {
        $id = $this->faker->numberBetween(100000, 999999);
        $phonenumber = $this->faker->phoneNumber();
        $authenticatables = $this->getAuthenticatables();

        foreach ($authenticatables as $ruleName => $authenticatable) {
            $requestInput = [
                'phonenumber_encrypted' => Crypt::encryptString($phonenumber),
                'phonenumber' => $phonenumber,
                'phonenumber_verified_at_encrypted' => Crypt::encryptString(strval((new \DateTime)->getTimestamp())),
                'password_confirmation' => $this->faker->lexify(),
            ];

            /** @var StoreDoctorAccountRequest|MockInterface $request */
            $request = Mockery::mock(StoreDoctorAccountRequest::class);
            $request->shouldReceive('safe->all')->andreturn($requestInput);

            $dsNewUser = $authenticatable->getDataStructure();
            $dsNewUserArray = $dsNewUser->toArray();

            /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
            $this->accountsManagement = Mockery::mock(AccountsManagement::class);
            $this->accountsManagement
                ->shouldReceive("createAccount")
                ->with(
                    Mockery::on(function (array $input) use ($requestInput) {
                        $this->assertArrayHasKey('phonenumber', $input);
                        $this->assertEquals($requestInput['phonenumber'], $input['phonenumber']);
                        $this->assertArrayHasKey('phonenumber_verified_at', $input);
                        $this->assertInstanceOf(\DateTime::class, $input['phonenumber_verified_at']);
                        return true;
                    }),
                    $this->dsUser,
                    $this->dataBaseCreateAccount
                )
                ->andReturn($dsNewUser);

            $accountsController = $this->instantiate();

            $jsonResponse = $accountsController->storeDoctor($request);
            $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
            $this->assertIsArray($jsonResponse->original);
            $this->assertCount(count($dsNewUserArray), $jsonResponse->original);

            foreach ($dsNewUserArray as $key => $value) {
                $this->assertArrayHasKey($key, $jsonResponse->original);
                $this->assertEquals($jsonResponse->original[$key], $value);
            }
        }
    }

    private function testStoreSecretary(): void
    {
        $id = $this->faker->numberBetween(100000, 999999);
        $phonenumber = $this->faker->phoneNumber();
        $authenticatables = $this->getAuthenticatables();

        foreach ($authenticatables as $ruleName => $authenticatable) {
            $requestInput = [
                'phonenumber_encrypted' => Crypt::encryptString($phonenumber),
                'phonenumber' => $phonenumber,
                'phonenumber_verified_at_encrypted' => Crypt::encryptString(strval((new \DateTime)->getTimestamp())),
                'password_confirmation' => $this->faker->lexify(),
            ];

            /** @var StoreSecretaryAccountRequest|MockInterface $request */
            $request = Mockery::mock(StoreSecretaryAccountRequest::class);
            $request->shouldReceive('safe->all')->andreturn($requestInput);

            $dsNewUser = $authenticatable->getDataStructure();
            $dsNewUserArray = $dsNewUser->toArray();

            /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
            $this->accountsManagement = Mockery::mock(AccountsManagement::class);
            $this->accountsManagement
                ->shouldReceive("createAccount")
                ->with(
                    Mockery::on(function (array $input) use ($requestInput) {
                        $this->assertArrayHasKey('phonenumber', $input);
                        $this->assertEquals($requestInput['phonenumber'], $input['phonenumber']);
                        $this->assertArrayHasKey('phonenumber_verified_at', $input);
                        $this->assertInstanceOf(\DateTime::class, $input['phonenumber_verified_at']);
                        return true;
                    }),
                    $this->dsUser,
                    $this->dataBaseCreateAccount
                )
                ->andReturn($dsNewUser);

            $accountsController = $this->instantiate();

            $jsonResponse = $accountsController->storeSecretary($request);
            $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
            $this->assertIsArray($jsonResponse->original);
            $this->assertCount(count($dsNewUserArray), $jsonResponse->original);

            foreach ($dsNewUserArray as $key => $value) {
                $this->assertArrayHasKey($key, $jsonResponse->original);
                $this->assertEquals($jsonResponse->original[$key], $value);
            }
        }
    }

    private function testStoreOperator(): void
    {
        $id = $this->faker->numberBetween(100000, 999999);
        $phonenumber = $this->faker->phoneNumber();
        $authenticatables = $this->getAuthenticatables();

        foreach ($authenticatables as $ruleName => $authenticatable) {
            $requestInput = [
                'phonenumber_encrypted' => Crypt::encryptString($phonenumber),
                'phonenumber' => $phonenumber,
                'phonenumber_verified_at_encrypted' => Crypt::encryptString(strval((new \DateTime)->getTimestamp())),
                'password_confirmation' => $this->faker->lexify(),
            ];

            /** @var StoreOperatorAccountRequest|MockInterface $request */
            $request = Mockery::mock(StoreOperatorAccountRequest::class);
            $request->shouldReceive('safe->all')->andreturn($requestInput);

            $dsNewUser = $authenticatable->getDataStructure();
            $dsNewUserArray = $dsNewUser->toArray();

            /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
            $this->accountsManagement = Mockery::mock(AccountsManagement::class);
            $this->accountsManagement
                ->shouldReceive("createAccount")
                ->with(
                    Mockery::on(function (array $input) use ($requestInput) {
                        $this->assertArrayHasKey('phonenumber', $input);
                        $this->assertEquals($requestInput['phonenumber'], $input['phonenumber']);
                        $this->assertArrayHasKey('phonenumber_verified_at', $input);
                        $this->assertInstanceOf(\DateTime::class, $input['phonenumber_verified_at']);
                        return true;
                    }),
                    $this->dsUser,
                    $this->dataBaseCreateAccount
                )
                ->andReturn($dsNewUser);

            $accountsController = $this->instantiate();

            $jsonResponse = $accountsController->storeOperator($request);
            $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
            $this->assertIsArray($jsonResponse->original);
            $this->assertCount(count($dsNewUserArray), $jsonResponse->original);

            foreach ($dsNewUserArray as $key => $value) {
                $this->assertArrayHasKey($key, $jsonResponse->original);
                $this->assertEquals($jsonResponse->original[$key], $value);
            }
        }
    }

    private function testStorePatient(): void
    {
        $id = $this->faker->numberBetween(100000, 999999);
        $phonenumber = $this->faker->phoneNumber();
        $authenticatables = $this->getAuthenticatables();

        foreach ($authenticatables as $ruleName => $authenticatable) {
            $requestInput = [
                'phonenumber_encrypted' => Crypt::encryptString($phonenumber),
                'phonenumber' => $phonenumber,
                'phonenumber_verified_at_encrypted' => Crypt::encryptString(strval((new \DateTime)->getTimestamp())),
                'password_confirmation' => $this->faker->lexify(),
            ];

            /** @var StorePatientAccountRequest|MockInterface $request */
            $request = Mockery::mock(StorePatientAccountRequest::class);
            $request->shouldReceive('safe->all')->andreturn($requestInput);

            $dsNewUser = $authenticatable->getDataStructure();
            $dsNewUserArray = $dsNewUser->toArray();

            /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
            $this->accountsManagement = Mockery::mock(AccountsManagement::class);
            $this->accountsManagement
                ->shouldReceive("createAccount")
                ->with(
                    Mockery::on(function (array $input) use ($requestInput) {
                        $this->assertArrayHasKey('phonenumber', $input);
                        $this->assertEquals($requestInput['phonenumber'], $input['phonenumber']);
                        $this->assertArrayHasKey('phonenumber_verified_at', $input);
                        $this->assertInstanceOf(\DateTime::class, $input['phonenumber_verified_at']);
                        return true;
                    }),
                    $this->dsUser,
                    $this->dataBaseCreateAccount
                )
                ->andReturn($dsNewUser);

            $accountsController = $this->instantiate();

            $jsonResponse = $accountsController->storePatient($request);
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

            /** @var UpdateAccountRequest|\Mockery\MockInterface $request */
            $request = Mockery::mock(UpdateAccountRequest::class);
            $request->shouldReceive('safe->all')->andReturn($input);

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

        /** @var UpdateAccountRequest|\Mockery\MockInterface $request */
        $request = Mockery::mock(UpdateAccountRequest::class);
        $request->shouldReceive('safe->all')->andReturn($input);

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
