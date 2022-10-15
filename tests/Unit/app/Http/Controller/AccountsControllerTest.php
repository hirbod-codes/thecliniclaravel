<?php

namespace Tests\Unit\app\Http\Controller;

use App\Http\Controllers\AccountsController;
use App\Auth\CheckAuthentication;
use App\Http\Requests\Accounts\AccountsCountRequest;
use App\Http\Requests\Accounts\IndexAccountsRequest;
use App\Http\Requests\Accounts\UpdateAccountRequest;
use App\Http\Requests\Accounts\StoreAccountRequest;
use App\Models\Auth\User as AuthUser;
use App\Models\User;
use App\UseCases\Accounts\AccountsManagement;
use Database\Interactions\Accounts\DataBaseCreateAccount;
use Database\Interactions\Accounts\DataBaseDeleteAccount;
use Database\Interactions\Accounts\DataBaseRetrieveAccounts;
use Database\Interactions\Accounts\DataBaseUpdateAccount;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Mockery;
use Mockery\MockInterface;
use App\UseCases\Accounts\Interfaces\IDataBaseCreateAccount;
use App\UseCases\Accounts\Interfaces\IDataBaseDeleteAccount;
use App\UseCases\Accounts\Interfaces\IDataBaseRetrieveAccounts;
use App\UseCases\Accounts\Interfaces\IDataBaseUpdateAccount;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;

/**
 * @covers \App\Http\Controllers\AccountsController
 */
class AccountsControllerTest extends TestCase
{
    use GetAuthenticatables;

    private Generator $faker;

    private string $ruleName;

    private AuthUser $user;

    /**
     * @var array<string, \App\Models\Auth\User> ['ruleName' => \App\Models\Auth\User, ...]
     */
    private array $users;

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

        /** @var \App\Auth\CheckAuthentication|\Mockery\MockInterface $checkAuthentication */
        $this->checkAuthentication = Mockery::mock(CheckAuthentication::class);

        /** @var \TheClinicUseCases\Accounts\Interfaces\IDataBaseCreateAccount|\Mockery\MockInterface $dataBaseRetrieveAccounts */
        $this->dataBaseRetrieveAccounts = Mockery::mock(IDataBaseRetrieveAccounts::class);

        /** @var \TheClinicUseCases\Accounts\Interfaces\IDataBaseDeleteAccount|\Mockery\MockInterface $dataBaseCreateAccount */
        $this->dataBaseCreateAccount = Mockery::mock(IDataBaseCreateAccount::class);

        /** @var \TheClinicUseCases\Accounts\Interfaces\IDataBaseRetrieveAccounts|\Mockery\MockInterface $dataBaseUpdateAccount */
        $this->dataBaseUpdateAccount = Mockery::mock(IDataBaseUpdateAccount::class);

        /** @var \TheClinicUseCases\Accounts\Interfaces\IDataBaseUpdateAccount|\Mockery\MockInterface $dataBaseDeleteAccount */
        $this->dataBaseDeleteAccount = Mockery::mock(IDataBaseDeleteAccount::class);

        /** @var \App\UseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
        $this->accountsManagement = Mockery::mock(AccountsManagement::class);

        $this->ruleName = $this->faker->randomElement(['admin', 'secretary', 'doctor', 'operator', 'patient']);
    }

    private function instantiate(): AccountsController
    {
        return new AccountsController(
            $this->checkAuthentication,
            $this->accountsManagement,
            $this->dataBaseRetrieveAccounts,
            $this->dataBaseCreateAccount,
            $this->dataBaseUpdateAccount,
            $this->dataBaseDeleteAccount,
        );
    }

    public function testIndex(): void
    {
        $count = $this->faker->numberBetween(1, 30);
        $lastAccountId = $this->faker->numberBetween(1, 1000);

        $this->dataBaseRetrieveAccounts->shouldReceive("getAccounts")
            ->once()
            ->with($count, $this->ruleName, $lastAccountId)
            ->andReturn(['a user']);

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
        $this->assertEquals('a user', $jsonResponse->original[0]);
    }

    public function testAccountsCount(): void
    {
        $validatedInput = [
            'roleName' => $this->ruleName,
        ];

        /** @var AccountsCountRequest|MockInterface $request */
        $request = Mockery::mock(AccountsCountRequest::class);
        $request
            ->shouldReceive('safe->all')
            ->andReturn($validatedInput)
            //
        ;

        $this->dataBaseRetrieveAccounts
            ->shouldReceive('getAccountsCount')
            ->once()
            ->with($this->ruleName)
            ->andReturn(5)
            //
        ;

        $response = $this->instantiate()->accountsCount($request);

        $this->assertInstanceOf(Response::class, $response);

        $this->assertEquals(5, $response->original);
    }

    public function testStore()
    {
        $validatedInput = ['phonenumber' => '09000000000'];

        /** @var Session|MockInterface $session */
        $session = Mockery::mock(Session::class);
        $session
            ->shouldReceive("get")
            ->with('isPhonenumberVerified')
            ->andReturn('132132132')
            //
        ;
        $session
            ->shouldReceive("get")
            ->with('phonenumber')
            ->andReturn(123)
            //
        ;

        /** @var StoreAccountRequest|MockInterface $request */
        $request = Mockery::mock(StoreAccountRequest::class);
        $request
            ->shouldReceive('safe->all')
            ->andReturn($validatedInput)
            //
        ;
        $request
            ->shouldReceive('session')
            ->andReturn($session)
            //
        ;

        $response = $this->instantiate()->store($request, $this->ruleName);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertIsString($response->original);
        $this->assertEquals(422, $response->getStatusCode());
        unset($response);

        $validatedInput['role'] = $this->ruleName;

        /** @var Session|MockInterface $session */
        $session = Mockery::mock(Session::class);
        $session
            ->shouldReceive("get")
            ->with('isPhonenumberVerified')
            ->andReturn('true')
            //
        ;
        $session
            ->shouldReceive("get")
            ->with('phonenumber')
            ->andReturn("09000000000")
            //
        ;
        $session
            ->shouldReceive("get")
            ->with('phonenumberVerifiedAt', 0)
            ->andReturn(strval($timestamp = (new \DateTime)->getTimestamp()))
            //
        ;

        $validatedInput['phonenumber_verified_at'] = (new \DateTime('now', new \DateTimeZone('UTC')))->setTimestamp($timestamp);

        /** @var StoreAccountRequest|MockInterface $request */
        $request = Mockery::mock(StoreAccountRequest::class);
        $request
            ->shouldReceive('safe->all')
            ->andReturn($validatedInput)
            //
        ;
        $request
            ->shouldReceive('session')
            ->andReturn($session)
            //
        ;

        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);
        $user
            ->shouldReceive('toArray')
            ->once()
            ->andReturn(['user'])
            //
        ;

        $this->dataBaseCreateAccount
            ->shouldReceive('createAccount')
            ->once()
            ->with($validatedInput)
            ->andReturn($user)
            //
        ;

        $response = $this->instantiate()->store($request, $this->ruleName);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('user', $response->original[0]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testShow(): void
    {
        $placeholder = '***';

        Validator::shouldReceive('make->fails')
            ->andReturn(false)
            //
        ;

        $this->accountsManagement
            ->shouldReceive('resolveUsername')
            ->once()
            ->with($placeholder)
            ->andReturn('username')
            //
        ;

        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);
        $user
            ->shouldReceive('toArray')
            ->once()
            ->andReturn(['user'])
            //
        ;

        $this->dataBaseRetrieveAccounts
            ->shouldReceive('getAccount')
            ->once()
            ->with('username')
            ->andReturn($user)
            //
        ;

        $response = $this->instantiate()->show($placeholder);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('user', $response->original[0]);
        $this->assertEquals(200, $response->getStatusCode());
        unset($response);
    }

    public function testShowSelf(): void
    {
        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);
        $user
            ->shouldReceive('getAttribute')
            ->andReturn('username')
            //
        ;
        $user
            ->shouldReceive('setAttribute')
            ->andReturn()
            //
        ;
        $user->username = 'username';
        $user
            ->shouldReceive('toArray')
            ->once()
            ->andReturn(['user'])
            //
        ;

        $this->checkAuthentication
            ->shouldReceive('getAuthenticated')
            ->once()
            ->andReturn($user)
            //
        ;
        $this->dataBaseRetrieveAccounts
            ->shouldReceive("getAccount")
            ->with($user->username)
            ->andReturn($user);

        $accountsController = $this->instantiate();

        $jsonResponse = $accountsController->showSelf();
        $this->assertInstanceOf(JsonResponse::class, $jsonResponse);
        $this->assertIsArray($jsonResponse->original);
        $this->assertCount(1, $jsonResponse->original);
        $this->assertEquals('user', $jsonResponse->original[0]);
        unset($jsonResponse);
    }

    public function testUpdate(): void
    {
        /** @var UpdateAccountRequest|MockInterface $request */
        $request = Mockery::mock(UpdateAccountRequest::class);
        $request
            ->shouldReceive('safe->all')
            ->andReturn(['input'])
            //
        ;

        $accountsController = $this->instantiate();

        $response = $accountsController->update($request, 0);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertIsString($response->original['message']);
        unset($response);

        /** @var UpdateAccountRequest|MockInterface $request */
        $request = Mockery::mock(UpdateAccountRequest::class);
        $request
            ->shouldReceive('safe->all')
            ->andReturn([])
            //
        ;

        $accountsController = $this->instantiate();

        $response = $accountsController->update($request, 10);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertIsString($response->original['message']);
        unset($response);

        /** @var UpdateAccountRequest|MockInterface $request */
        $request = Mockery::mock(UpdateAccountRequest::class);
        $request
            ->shouldReceive('safe->all')
            ->andReturn(['input'])
            //
        ;

        $this->accountsManagement
            ->shouldReceive('resolveUsername')
            ->once()
            ->with(10)
            ->andReturn('username')
            //
        ;

        /** @var User|MockInterface $targetUser */
        $targetUser = Mockery::mock(User::class);

        $this->dataBaseRetrieveAccounts
            ->shouldReceive('getAccount')
            ->once()
            ->with('username')
            ->andReturn($targetUser)
            //
        ;

        /** @var User|MockInterface $updatedUser */
        $updatedUser = Mockery::mock(User::class);
        $updatedUser
            ->shouldReceive('toArray')
            ->once()
            ->andReturn(['user'])
            //
        ;

        $this->dataBaseUpdateAccount
            ->shouldReceive('massUpdateAccount')
            ->once()
            ->with(['input'], $targetUser)
            ->andReturn($updatedUser)
            //
        ;

        $accountsController = $this->instantiate();

        $response = $accountsController->update($request, 10);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertCount(1, $response->original);
        $this->assertEquals('user', $response->original[0]);
        unset($response);
    }

    public function testDestroy(): void
    {
        $accountsController = $this->instantiate();

        $response = $accountsController->destroy(0);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertIsArray($response->original);
        $this->assertIsString($response->original['message']);
        unset($response);

        $this->accountsManagement
            ->shouldReceive('resolveUsername')
            ->once()
            ->with(10)
            ->andReturn('username')
            //
        ;

        /** @var User|MockInterface $targetUser */
        $targetUser = Mockery::mock(User::class);

        $this->dataBaseRetrieveAccounts
            ->shouldReceive('getAccount')
            ->once()
            ->with('username')
            ->andReturn($targetUser)
            //
        ;

        $this->dataBaseDeleteAccount
            ->shouldReceive('deleteAccount')
            ->once()
            ->with($targetUser)
            //
        ;

        $accountsController = $this->instantiate();

        $response = $accountsController->destroy(10);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsString($response->original);
        unset($response);
    }
}
