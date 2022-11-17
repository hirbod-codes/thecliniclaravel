<?php

namespace Tests\Unit\app\Http\Controller;

use Database\Interactions\Privileges\PrivilegesManagement;
use App\Auth\CheckAuthentication;
use App\Http\Controllers\AuthController;
use App\Http\Requests\Accounts\SendCodeToEmailRequest;
use App\Http\Requests\Accounts\SendCodeToPhonenumberRequest;
use App\Http\Requests\Accounts\SendPhonenumberVerificationCodeRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdatePhonenumberRequest;
use App\Http\Requests\VerifyPhonenumberRequest;
use App\Http\Requests\VerifyPhonenumberVerificationCodeRequest;
use App\Models\User;
use App\Notifications\SendEmailPasswordResetCode;
use App\Notifications\SendPhonenumberPasswordResetCode;
use App\Notifications\SendPhonenumberVerificationCode;
use Database\Interactions\Accounts\AccountsManagement;
use Database\Interactions\Accounts\DataBaseCreateAccount;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Mockery\MockInterface;
use Database\Interactions\Accounts\Interfaces\IDataBaseCreateAccount;
use Database\Interactions\Accounts\Interfaces\IDataBaseRetrieveAccounts;
use Illuminate\Http\RedirectResponse;
use Tests\TestCase;

/**
 * @covers \App\Http\Controllers\AuthController
 */
class AuthControllerTest extends TestCase
{
    private Generator $faker;

    private CheckAuthentication|MockInterface $checkAuthentication;

    private AccountsManagement|MockInterface $accountsManagement;

    private DataBaseCreateAccount|MockInterface $dataBaseCreateAccount;
    private IDataBaseRetrieveAccounts|MockInterface $databaseRetrieveAccounts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        /** @var \App\Auth\CheckAuthentication|\Mockery\MockInterface $checkAuthentication */
        $this->checkAuthentication = Mockery::mock(CheckAuthentication::class);

        /** @var \Database\Interactions\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
        $this->accountsManagement = Mockery::mock(AccountsManagement::class);

        /** @var \Database\Interactions\Accounts\Interfaces\IDataBaseDeleteAccount|\Mockery\MockInterface $dataBaseCreateAccount */
        $this->dataBaseCreateAccount = Mockery::mock(IDataBaseCreateAccount::class);

        /** @var \Database\Interactions\Accounts\Interfaces\IDataBaseRetrieveAccounts|\Mockery\MockInterface $databaseRetrieveAccounts */
        $this->databaseRetrieveAccounts = Mockery::mock(IDatabaseRetrieveAccounts::class);
    }

    private function instantiate(): AuthController
    {
        return new AuthController(
            $this->checkAuthentication,
            $this->accountsManagement,
            $this->dataBaseCreateAccount,
            $this->databaseRetrieveAccounts,
        );
    }

    public function testRegister(): void
    {
        $timestamp = (new \DateTime)->getTimestamp();

        $validatedInput = [];
        $validatedInput['role'] = 'patient';
        $validatedInput['userAccountAttributes'] = ['attribute' => 'value'];
        $validatedInput['userAttributes']['phonenumber'] = '09000000000';
        $validatedInput['userAttributes']['password'] = 'password';
        $validatedInput['userAttributes']['username'] = 'username';
        $validatedInput['userAttributes']['phonenumber_verified_at'] = (new \DateTime('now', new \DateTimeZone('UTC')))->setTimestamp($timestamp);

        /** @var Session|MockInterface $session */
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('get')->with('phonenumber')->andReturn('09000000000');
        $session->shouldReceive('get')->with('phonenumber_verification_timestamp', Mockery::on(function ($value) {
            return is_int($value);
        }))
            ->andReturn(strval($timestamp));
        $session->shouldReceive('get')->with('redirecturl');
        $session->shouldReceive('forget')->with('redirecturl');

        /** @var RegisterUserRequest|MockInterface $request */
        $request = Mockery::mock(RegisterUserRequest::class);
        $request->shouldReceive('all')->andReturn($validatedInput);
        $request->shouldReceive('session')->andReturn($session);

        $this->dataBaseCreateAccount->shouldReceive('createAccount')->once()->with('patient', 'patient', $validatedInput['userAttributes'], $validatedInput['userAccountAttributes']);

        Auth::shouldReceive('guard->attempt')->with(['password' => 'password', 'username' => 'username'], false);

        $response = $this->instantiate()->register($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testSendCodeToPhonenumber(): void
    {
        $validatedInput = [];
        $validatedInput['phonenumber'] = '09000000000';

        /** @var Session|MockInterface $session */
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('get')->with('code_destination')->andReturn('phonenumber');
        $session->shouldReceive('get')->with('phonenumber')->andReturn('09000000000');
        $session->shouldReceive('get')->with('code_expiration_timestamp')->andReturn(strval($timestamp = (new \DateTime)->getTimestamp()));
        $session->shouldReceive('get')->with('code_expiration_timestamp', 0)->andReturn(strval($timestamp = (new \DateTime)->getTimestamp()));
        $session->shouldReceive('forget')->with(['code_destination', 'code', 'code_expiration_timestamp']);

        Notification::fake();

        $session->shouldReceive('put')->with('code_destination', 'phonenumber');
        $session->shouldReceive('put')->with('code', Mockery::on(function ($value) {
            return is_int($value);
        }));
        $session->shouldReceive('put')->with('phonenumber', $validatedInput['phonenumber']);
        $session->shouldReceive('put')->with('code_expiration_timestamp', Mockery::on(function ($value) {
            return is_int($value);
        }));

        /** @var SendCodeToPhonenumberRequest|MockInterface $request */
        $request = Mockery::mock(SendCodeToPhonenumberRequest::class);
        $request->shouldReceive('safe->all')->andReturn($validatedInput);
        $request->shouldReceive('session')->andReturn($session);

        $response = $this->instantiate()->sendCodeToPhonenumber($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsString($response->original);
    }

    public function testSendCodeToEmail(): void
    {
        $validatedInput = [];
        $validatedInput['email'] = 'email';

        /** @var Session|MockInterface $session */
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('get')->with('code_destination')->andReturn('phonenumber');
        $session->shouldReceive('get')->with('code_destination')->andReturn('email');
        $session->shouldReceive('get')->with('code_expiration_timestamp')->andReturn(strval($timestamp = (new \DateTime)->getTimestamp()));
        $session->shouldReceive('get')->with('code_expiration_timestamp', 0)->andReturn(strval($timestamp = (new \DateTime)->getTimestamp()));
        $session->shouldReceive('forget')->with(['code_destination', 'code', 'code_expiration_timestamp']);

        $this->accountsManagement->shouldReceive('resolveUsername')->once()->with($validatedInput['email'])->andReturn('username');

        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);
        $user->shouldReceive('notify');

        $this->databaseRetrieveAccounts->shouldReceive('getAccount')->once()->with('username')->andReturn($user);

        $session->shouldReceive('put')->with('code_destination', 'email');
        $session->shouldReceive('put')->with('code', Mockery::on(function ($value) {
            return is_int($value);
        }));
        $session->shouldReceive('put')->with('email', 'email');
        $session->shouldReceive('put')->with('code_expiration_timestamp', Mockery::on(function ($value) {
            return is_int($value);
        }));

        /** @var SendCodeToEmailRequest|MockInterface $request */
        $request = Mockery::mock(SendCodeToEmailRequest::class);
        $request->shouldReceive('safe->all')->andReturn($validatedInput);
        $request->shouldReceive('session')->andReturn($session);

        $response = $this->instantiate()->sendCodeToEmail($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsString($response->original);
    }

    public function testVerifyPhonenumber(): void
    {
        $requestInput = [
            'code' => $code = $this->faker->numberBetween(100000, 999999),
            'phonenumber' => $phonenumber = '09000000000',
        ];

        /** @var Session|MockInterface $session */
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('get')->with('code_destination')->andReturn('phonenumber');
        $session->shouldReceive('get')->with('phonenumber', 0)->andReturn($phonenumber);
        $session->shouldReceive('get')->with('code', 0)->andReturn($code);
        $session->shouldReceive('get')->with('code_expiration_timestamp', 0)->andReturn((new \DateTime)->modify("+1 minute")->getTimestamp());
        $session->shouldReceive('forget')->with(['code_destination', 'code', 'code_expiration_timestamp']);
        $session->shouldReceive('put')->with('isPhonenumberVerified', 1);
        $session->shouldReceive('put')->with('phonenumberVerifiedAt', Mockery::on(function ($value) {
            return is_int($value);
        }));

        /** @var VerifyPhonenumberRequest|MockInterface $request */
        $request = Mockery::mock(VerifyPhonenumberRequest::class);
        $request->shouldReceive('safe->all')->andReturn($requestInput);
        $request->shouldReceive('session')->andReturn($session);

        $response = $this->instantiate()->verifyPhonenumber($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsString($response->original);
        $this->assertEquals(trans_choice('auth.phonenumber_verification_successful', 0), $response->original);
    }

    public function testUpdatePhonenumber(): void
    {
        $requestInput = [
            'newPhonenumber' => '09000000001',
            'phonenumber' => $phonenumber = '09000000000',
        ];

        /** @var Session|MockInterface $session */
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('get')->with('isPhonenumberVerified')->andReturn('phonenumber');
        $session->shouldReceive('get')->with('phonenumber')->andReturn($phonenumber);
        $session->shouldReceive('forget')->with(['code_destination', 'code', 'code_expiration_timestamp']);

        /** @var UpdatePhonenumberRequest|MockInterface $request */
        $request = Mockery::mock(UpdatePhonenumberRequest::class);
        $request->shouldReceive('safe->all')->andReturn($requestInput);
        $request->shouldReceive('session')->andReturn($session);

        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);
        $user->shouldReceive('saveOrFail')->once();
        $user->shouldReceive('setAttribute');
        $user->shouldReceive('__set');

        $this->checkAuthentication->shouldReceive('getAuthenticated')->once()->andReturn($user);

        $response = $this->instantiate()->updatePhonenumber($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsString($response->original);
        $this->assertEquals(trans_choice('auth.phonenumber_update_success', 0), $response->original);
    }

    public function testResetPassword(): void
    {
        $requestInput = [
            'code' => '000000',
            'password' => 'password',
        ];
        $identifier = 'phonenumber';

        /** @var Session|MockInterface $session */
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('get')->with('code', 0)->andReturn('000000');
        $session->shouldReceive('get')->with('code_expiration_timestamp', 0)->andReturn((new \DateTime)->modify("+1 minute")->getTimestamp());
        $session->shouldReceive('get')->with('code_destination')->andReturn($identifier);
        $session->shouldReceive('get')->with($identifier)->andReturn($identifier);
        $session->shouldReceive('forget')->with(['code_destination', 'code', 'code_expiration_timestamp']);

        /** @var ResetPasswordRequest|MockInterface $request */
        $request = Mockery::mock(ResetPasswordRequest::class);
        $request->shouldReceive('safe->all')->andReturn($requestInput);
        $request->shouldReceive('session')->andReturn($session);

        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);
        $user->shouldReceive('saveOrFail')->once();
        $user->shouldReceive('setAttribute');
        $user->shouldReceive('__set');

        $this->accountsManagement->shouldReceive('resolveUsername')->once()->with($identifier)->andReturn('username');
        $this->databaseRetrieveAccounts->shouldReceive('getAccount')->once()->with('username')->andReturn($user);

        $response = $this->instantiate()->resetPassword($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertIsString($response->original);
        $this->assertEquals(trans_choice('auth.password_update_success', 0), $response->original);
    }
}
