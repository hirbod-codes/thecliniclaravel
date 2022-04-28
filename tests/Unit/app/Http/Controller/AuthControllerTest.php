<?php

namespace Tests\Unit\app\Http\Controller;

use TheClinicUseCases\Privileges\PrivilegesManagement;
use App\Auth\CheckAuthentication;
use App\Http\Controllers\AuthController;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\SendPhonenumberVerificationCodeRequest;
use App\Http\Requests\VerifyPhonenumberVerificationCodeRequest;
use App\Models\User;
use App\Notifications\SendEmailPasswordResetCode;
use App\Notifications\SendPhonenumberPasswordResetCode;
use App\Notifications\SendPhonenumberVerificationCode;
use TheClinicUseCases\Accounts\AccountsManagement;
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
use TheClinicUseCases\Accounts\Interfaces\IDataBaseCreateAccount;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use TheClinicUseCases\Accounts\Authentication;

class AuthControllerTest extends TestCase
{
    use GetAuthenticatables;

    private Generator $faker;

    private Authentication|MockInterface $authentication;

    private PrivilegesManagement|MockInterface $privilegesManagement;

    private CheckAuthentication|MockInterface $checkAuthentication;

    private AccountsManagement|MockInterface $accountsManagement;

    private DataBaseCreateAccount|MockInterface $dataBaseCreateAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        /** @var \TheClinicUseCases\Accounts\Authentication|\Mockery\MockInterface $authentication */
        $this->authentication = Mockery::mock(Authentication::class);

        /** @var \TheClinicUseCases\Privileges\PrivilegesManagement|\Mockery\MockInterface $privilegesManagement */
        $this->privilegesManagement = Mockery::mock(PrivilegesManagement::class);

        /** @var \App\Auth\CheckAuthentication|\Mockery\MockInterface $checkAuthentication */
        $this->checkAuthentication = Mockery::mock(CheckAuthentication::class);

        /** @var \TheClinicUseCases\Privileges\PrivilegesManagement|\Mockery\MockInterface $privilegesManagement */
        $this->privilegesManagement = Mockery::mock(PrivilegesManagement::class);

        /** @var \TheClinicUseCases\Accounts\Interfaces\IDataBaseDeleteAccount|\Mockery\MockInterface $dataBaseCreateAccount */
        $this->dataBaseCreateAccount = Mockery::mock(IDataBaseCreateAccount::class);

        /** @var \TheClinicUseCases\Accounts\AccountsManagement|\Mockery\MockInterface $accountsManagement */
        $this->accountsManagement = Mockery::mock(AccountsManagement::class);
    }

    private function instantiate(): AuthController
    {
        return new AuthController(
            $this->authentication,
            $this->privilegesManagement,
            $this->checkAuthentication,
            $this->accountsManagement,
            $this->dataBaseCreateAccount,
        );
    }

    public function testRegister(): void
    {
        $ruleName = 'patient';
        $timestamp = (new \DateTime)->getTimestamp();
        $requestInput = [
            'phonenumber' => $phonenumber = $this->faker->phoneNumber(),
        ];

        /** @var Session|MockInterface $session */
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('get')->with('phonenumber')->andreturn($phonenumber);
        $session->shouldReceive('get')->with('phonenumber_verification_timestamp', Mockery::on(function (int $value) {
            return true;
        }))->andreturn($timestamp);
        $session->shouldReceive('get')->with('redirecturl')->andreturn(null);
        $session->shouldReceive('forget')->with('redirecturl');

        /** @var RegisterUserRequest|MockInterface $request */
        $request = Mockery::mock(RegisterUserRequest::class);
        $request->shouldReceive('safe->all')->andreturn($requestInput);
        $request->shouldReceive('session')->andreturn($session);

        $this->accountsManagement
            ->shouldReceive('signupAccount')
            ->with(Mockery::on(function (array $input) use ($phonenumber) {
                $this->assertArrayHasKey('role', $input);
                $this->assertEquals('patient', $input['role']);

                $this->assertArrayHasKey('phonenumber', $input);
                $this->assertEquals($phonenumber, $input['phonenumber']);

                $this->assertArrayHasKey('phonenumber_verified_at', $input);
                $this->assertInstanceOf(\DateTime::class, $input['phonenumber_verified_at']);

                return true;
            }), $this->dataBaseCreateAccount, $this->checkAuthentication)
            ->andReturn(($authenticatable = $this->getAuthenticatable($ruleName))->getDataStructure());

        $response = $this->instantiate()->register($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertIsArray($response->original);
        $this->assertCount(2, $response->original);

        $this->assertArrayHasKey('message', $response->original);
        $this->assertEquals(trans_choice('auth.registration_successful', 0), $response->original['message']);

        $this->assertArrayHasKey('redirecturl', $response->original);
        $this->assertEquals('/', $response->original['redirecturl']);
    }

    public function testForgotPasswordPhonenumber(): void
    {
        $requestInput = [
            'phonenumber' => $phonenumber = ($user = $this->getAuthenticatable('patient')->user)->phonenumber,
        ];

        /** @var Session|MockInterface $session */
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('forget')->with(['code', 'email', 'phonenumber', 'password_reset_verification_timestamp']);

        $session->shouldReceive('put')->with('code', Mockery::on(function (int $value) {
            return true;
        }));
        $session->shouldReceive('put')->with('phonenumber', $phonenumber);
        $session->shouldReceive('put')->with('password_reset_verification_timestamp', Mockery::on(function (int $value) {
            return true;
        }));

        Notification::fake();

        /** @var ForgotPasswordRequest|MockInterface $request */
        $request = Mockery::mock(ForgotPasswordRequest::class);
        $request->shouldReceive('safe->all')->andreturn($requestInput);
        $request->shouldReceive('session')->andreturn($session);

        $response = $this->instantiate()->forgotPassword($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(trans_choice('auth.phonenumber_verification_code_sent', 0), $response->original);

        Notification::assertSentTo(
            [$user],
            SendPhonenumberPasswordResetCode::class
        );
    }

    public function testForgotPasswordEmail(): void
    {
        $requestInput = [
            'email' => $email = ($user = $this->getAuthenticatable('patient')->user)->email,
        ];

        /** @var Session|MockInterface $session */
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('forget')->with(['code', 'email', 'phonenumber', 'password_reset_verification_timestamp']);

        $session->shouldReceive('put')->with('code', Mockery::on(function (int $value) {
            return true;
        }));
        $session->shouldReceive('put')->with('email', $email);
        $session->shouldReceive('put')->with('password_reset_verification_timestamp', Mockery::on(function (int $value) {
            return true;
        }));

        Notification::fake();

        /** @var ForgotPasswordRequest|MockInterface $request */
        $request = Mockery::mock(ForgotPasswordRequest::class);
        $request->shouldReceive('safe->all')->andreturn($requestInput);
        $request->shouldReceive('session')->andreturn($session);

        $response = $this->instantiate()->forgotPassword($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(trans_choice('auth.email_verification_code_sent', 0), $response->original);

        Notification::assertSentTo(
            [$user],
            SendEmailPasswordResetCode::class
        );
    }

    public function testResetPasswordEmail(): void
    {
        $code = $this->faker->numberBetween(100000, 999999);
        $password = $this->faker->password(8);

        $requestInput = [
            'code' => $code,
            'password' => $password,
            'email' => $email = ($user = $this->getAuthenticatable('patient')->user)->email,
        ];

        /** @var Session|MockInterface $session */
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('get')->with('phonenumber')->andReturn($user->phonenumber);
        $session->shouldReceive('get')->with('email')->andReturn($email);
        $session->shouldReceive('get')->with('code', 0)->andReturn($code);
        $session->shouldReceive('get')->with('password_reset_verification_timestamp', Mockery::on(function (int $value) {
            return true;
        }))->andReturn((new \DateTime)->getTimestamp());
        $session->shouldReceive('forget')->with(['code', 'email', 'phonenumber', 'password_reset_verification_timestamp']);

        $session->shouldReceive('get')->with('redirecturl')->andReturn(null);
        $session->shouldReceive('forget')->with('redirecturl');

        /** @var ResetPasswordRequest|MockInterface $request */
        $request = Mockery::mock(ResetPasswordRequest::class);
        $request->shouldReceive('safe->all')->andReturn($requestInput);
        $request->shouldReceive('session')->andReturn($session);

        Auth::shouldReceive('guard->user->tokens->get')->andReturn([]);
        Auth::shouldReceive('guard->logout');

        $this->checkAuthentication
            ->shouldReceive('getAuthenticated')
            ->andReturn($this->getAuthenticatable('patient'));

        $response = $this->instantiate()->resetPassword($request);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $this->assertIsArray($response->original);
        $this->assertCount(2, $response->original);

        $this->assertArrayHasKey('message', $response->original);
        $this->assertEquals(trans_choice('auth.password-reset-successful', 0), $response->original['message']);

        $this->assertArrayHasKey('redirecturl', $response->original);
        $this->assertEquals('/', $response->original['redirecturl']);
    }

    public function testResetPasswordPhonenumber(): void
    {
        $code = $this->faker->numberBetween(100000, 999999);
        $password = $this->faker->password(8);

        $requestInput = [
            'code' => $code,
            'password' => $password,
            'phonenumber' => $phonenumber = ($user = $this->getAuthenticatable('patient')->user)->phonenumber,
        ];

        /** @var Session|MockInterface $session */
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('get')->with('phonenumber')->andReturn($phonenumber);
        $session->shouldReceive('get')->with('email')->andReturn($user->email);
        $session->shouldReceive('get')->with('code', 0)->andReturn($code);
        $session->shouldReceive('get')->with('password_reset_verification_timestamp', Mockery::on(function (int $value) {
            return true;
        }))->andReturn((new \DateTime)->getTimestamp());
        $session->shouldReceive('forget')->with(['code', 'email', 'phonenumber', 'password_reset_verification_timestamp']);

        $session->shouldReceive('get')->with('redirecturl')->andReturn(null);
        $session->shouldReceive('forget')->with('redirecturl');

        /** @var ResetPasswordRequest|MockInterface $request */
        $request = Mockery::mock(ResetPasswordRequest::class);
        $request->shouldReceive('safe->all')->andReturn($requestInput);
        $request->shouldReceive('session')->andReturn($session);

        Auth::shouldReceive('guard->user->tokens->get')->andReturn([]);
        Auth::shouldReceive('guard->logout');

        $this->checkAuthentication
            ->shouldReceive('getAuthenticated')
            ->andReturn($this->getAuthenticatable('patient'));

        $response = $this->instantiate()->resetPassword($request);
        $this->assertInstanceOf(JsonResponse::class, $response);

        $this->assertIsArray($response->original);
        $this->assertCount(2, $response->original);

        $this->assertArrayHasKey('message', $response->original);
        $this->assertEquals(trans_choice('auth.password-reset-successful', 0), $response->original['message']);

        $this->assertArrayHasKey('redirecturl', $response->original);
        $this->assertEquals('/', $response->original['redirecturl']);
    }

    public function testSendPhonenumberVerificationCode(): void
    {
        $requestInput = [
            'phonenumber' => $phonenumber = ($user = $this->getAuthenticatable('patient')->user)->phonenumber,
        ];

        /** @var Session|MockInterface $session */
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('put')->with('code', Mockery::on(function (int $val) {
            return true;
        }));
        $session->shouldReceive('put')->with('phonenumber', $phonenumber);
        $session->shouldReceive('put')->with('phonenumber_verification_timestamp', Mockery::on(function (int $val) {
            return true;
        }));
        $session->shouldReceive('forget')->with(['code', 'phonenumber', 'phonenumber', 'password_reset_verification_timestamp']);

        Notification::fake();

        /** @var SendPhonenumberVerificationCodeRequest|MockInterface $request */
        $request = Mockery::mock(SendPhonenumberVerificationCodeRequest::class);
        $request->shouldReceive('safe->all')->andReturn($requestInput);
        $request->shouldReceive('session')->andReturn($session);

        $response = $this->instantiate()->sendPhonenumberVerificationCode($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(trans_choice('auth.phonenumber_verification_code_sent', 0), $response->original);
        Notification::assertSentTo(
            [(new AnonymousNotifiable)->route('sms', $phonenumber)],
            SendPhonenumberVerificationCode::class
        );
    }

    public function testVerifyPhonenumberVerificationCode(): void
    {
        $requestInput = [
            'code' => $code = $this->faker->numberBetween(100000, 999999),
            'phonenumber' => $phonenumber = ($user = $this->getAuthenticatable('patient')->user)->phonenumber,
        ];

        /** @var Session|MockInterface $session */
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('get')->with('code')->andReturn($code);
        $session->shouldReceive('get')->with('phonenumber')->andReturn($phonenumber);
        $session->shouldReceive('get')->with('phonenumber_verification_timestamp', Mockery::on(function (int $value) {
            return true;
        }))->andReturn((new \DateTime)->getTimestamp());
        $session->shouldReceive('forget')->with(['code']);

        /** @var VerifyPhonenumberVerificationCodeRequest|MockInterface $request */
        $request = Mockery::mock(VerifyPhonenumberVerificationCodeRequest::class);
        $request->shouldReceive('safe->all')->andReturn($requestInput);
        $request->shouldReceive('session')->andReturn($session);

        $response = $this->instantiate()->verifyPhonenumberVerificationCode($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(trans_choice('auth.phonenumber_verification_successful', 0), $response->original);
    }
}
