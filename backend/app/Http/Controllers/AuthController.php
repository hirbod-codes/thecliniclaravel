<?php

namespace App\Http\Controllers;

use App\Auth\CheckAuthentication;
use App\Http\Requests\Accounts\PhonenumberAvailabilityRequest;
use App\Http\Requests\Accounts\SendCodeToEmailRequest;
use App\Http\Requests\Accounts\SendCodeToPhonenumberRequest;
use App\Http\Requests\LogInRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdatePhonenumberRequest;
use App\Http\Requests\VerifyPhonenumberRequest;
use App\Models\User;
use App\Notifications\SendEmailPasswordResetCode;
use App\Notifications\SendPhonenumberVerificationCode;
use Database\Interactions\Accounts\DataBaseCreateAccount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\TokenRepository;
use Database\Interactions\Accounts\AccountsManagement;
use Database\Interactions\Accounts\Interfaces\IDataBaseCreateAccount;
use Database\Interactions\Accounts\Interfaces\IDataBaseRetrieveAccounts;
use Database\Interactions\Accounts\DataBaseRetrieveAccounts;

class AuthController extends Controller
{
    private CheckAuthentication|null $checkAuthentication;

    private AccountsManagement|null $accountsManagement;

    private IDataBaseCreateAccount $dataBaseCreateAccount;
    private IDataBaseRetrieveAccounts $databaseRetrieveAccounts;

    public function __construct(
        CheckAuthentication|null $checkAuthentication = null,
        AccountsManagement|null $accountsManagement = null,
        IDataBaseCreateAccount|null $dataBaseCreateAccount = null,
        IDataBaseRetrieveAccounts|null $databaseRetrieveAccounts = null,
    ) {
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;
        $this->accountsManagement = $accountsManagement ?: new AccountsManagement();

        $this->dataBaseCreateAccount = $dataBaseCreateAccount ?: new DataBaseCreateAccount;
        $this->databaseRetrieveAccounts = $databaseRetrieveAccounts ?: new DataBaseRetrieveAccounts;
    }

    public function logout(): Redirector|RedirectResponse
    {
        Auth::guard('web')->logout();

        session()->invalidate();

        session()->regenerateToken();

        return redirect('/');
    }

    public function apiLogout(): JsonResponse
    {
        $tokens = $this->getTokens();

        foreach ($tokens as $token) {
            $tokenRepository = app(TokenRepository::class);
            $tokenRepository->revokeAccessToken($token->getKey());

            $refreshTokenRepository = app(RefreshTokenRepository::class);
            $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->getKey());
        }

        return response()->json(['message' => 'Successfully loged you out.'], 200);
    }

    private function getTokens(): Collection|array
    {
        /** @var User $user */
        $user = Auth::guard('api')->user() ?: Auth::guard('web')->user();
        return $user->tokens()->get();
    }

    public function apiLogin(LogInRequest $request): Response|JsonResponse
    {
        $identifier = $this->collectIdentifier($credentials = $request->safe()->all());

        /** @var User $user */
        $user = User::query()
            ->where($identifier, '=', $credentials[$identifier])
            ->first()
            //
        ;

        if ($user === null) {
            return response()->json(['message' => __('auth.failed')], 422);
        } elseif (!Hash::check($credentials['password'], $user->getAuthPassword())) {
            return response()->json(['message' => __('auth.password')], 422);
        } else {
            $tokens = DB::table('oauth_access_tokens')
                ->where('user_id', '=', $user->getKey())
                ->where('revoked', '=', 0)
                ->where('expires_at', '>', new \DateTime('now', new \DateTimeZone('UTC')))
                ->get()
                //
            ;

            if (count($tokens) > 0) {
                return response()->json(['message' => trans_choice('auth.access_token_limit', 0)], 422);
            }

            $token = $user->createToken($user->getKey(), ['*'])->accessToken;

            return response()->json(['access_token' => $token]);
        }
    }

    private function collectIdentifier(array $credentials): string
    {
        if (array_key_exists('email', $credentials)) {
            $identifier = 'email';
        } else {
            $identifier = 'username';
        }

        return $identifier;
    }

    public function login(LogInRequest $request): Response|Redirector|RedirectResponse|JsonResponse
    {
        $remember = false;
        $credentials = $request->safe()->all();
        if (array_key_exists('remember', $credentials)) {
            $remember = $credentials['remember'];
        }
        unset($credentials['remember']);

        if (!Auth::guard('web')->attempt($credentials, $remember)) {
            if ($request->header('content-type') === 'application/json') {
                return response()->json(['message' => __('auth.failed')], 422);
            } else {
                return response(__('auth.failed'), 422);
            }
        }

        return redirect('/');
    }

    public function register(RegisterUserRequest $request): Response|Redirector|RedirectResponse|JsonResponse
    {
        $validatedInput = $request->all();
        $session = $request->session();

        if ($validatedInput['userAttributes']['phonenumber'] !== $session->get('phonenumber')) {
            if ($request->header('content-type') === 'application/json') {
                return response()->json(['message' => trans_choice('auth.phonenumber_verification_mismatch', 0)], 422);
            } else {
                return response(trans_choice('auth.phonenumber_verification_mismatch', 0), 422);
            }
        }

        $timestamp = intval($session->get('phonenumber_verification_timestamp', (new \DateTime)->getTimestamp()));
        $validatedInput['userAttributes']['phonenumber_verified_at'] = (new \DateTime('now', new \DateTimeZone('UTC')))->setTimestamp($timestamp);

        $this->dataBaseCreateAccount->createAccount('patient', 'patient', $validatedInput['userAttributes'], $validatedInput['userAccountAttributes']);

        Auth::guard('web')->attempt(['password' => $validatedInput['userAttributes']['password'], 'username' => $validatedInput['userAttributes']['username']], false);

        $redirecturl = $session->get('redirecturl');
        $session->forget('redirecturl');

        return redirect($redirecturl === null ? '/' : $redirecturl);
    }

    public function phonenumberAvailability(PhonenumberAvailabilityRequest $request): Response
    {
        $input = $request->safe()->all();
        return response($input['phonenumber']);
    }

    public function sendCodeToPhonenumber(SendCodeToPhonenumberRequest $request): Response
    {
        $validatedInput = $request->safe()->all();
        $code = $this->generateCode();

        $session = $request->session();
        if (
            $session->get('code_destination') !== null &&
            $session->get('code_destination') === 'phonenumber' &&
            $session->get('phonenumber') !== null &&
            $session->get('phonenumber') === $validatedInput['phonenumber'] &&
            $session->get('code_expiration_timestamp') !== null &&
            (new \DateTime)->getTimestamp() < intval($session->get('code_expiration_timestamp', 0))
        ) {
            return response(trans_choice('auth.verification_code_not_expired', 0), 422);
        }
        $session->forget(['code_destination', 'code', 'code_expiration_timestamp']);

        // Notification::route('phonenumber', $validatedInput['phonenumber'])->notify(new SendPhonenumberVerificationCode($code));

        $session->put('code_destination', 'phonenumber');
        $session->put('code', $code);
        $session->put('phonenumber', $validatedInput['phonenumber']);
        $session->put('code_expiration_timestamp', (new \DateTime)->modify('+60 seconds')->getTimestamp());

        return response(trans_choice('auth.phonenumber_verification_code_sent', 0));
    }

    public function sendCodeToEmail(SendCodeToEmailRequest $request): Response
    {
        $input = $request->safe()->all();
        $code = $this->generateCode();

        $session = $request->session();
        if (
            $session->get('code_destination') &&
            $session->get('code_destination') === 'email' &&
            $session->get('code_expiration_timestamp') &&
            ((new \DateTime)->getTimestamp() < intval($session->get('code_expiration_timestamp', 0)))
        ) {
            return response(trans_choice('auth.verification_code_not_expired', 0), 422);
        }
        $session->forget(['code_destination', 'code', 'code_expiration_timestamp']);

        $user = $this->databaseRetrieveAccounts->getAccount($this->accountsManagement->resolveUsername($input['email']));
        $user->notify(new SendEmailPasswordResetCode($code));

        $session->put('code_destination', 'email');
        $session->put('code', $code);
        $session->put('email', $input['email']);
        $session->put('code_expiration_timestamp', (new \DateTime)->modify('+90 seconds')->getTimestamp());

        return response(trans_choice('auth.email_verification_code_sent', 0));
    }

    private function generateCode(): int
    {
        return 333222;
        return rand(100000, 999999);
    }

    public function verifyPhonenumber(VerifyPhonenumberRequest $request): Response
    {
        $input = $request->safe()->all();
        $session = $request->session();

        if (
            ($t0 = $session->get('code_destination')) !== 'phonenumber' ||
            (intval(($t1 = $session->get('phonenumber', 0))) !== intval($input['phonenumber']))
        ) {
            $session->forget(['code_destination', 'code', 'code_expiration_timestamp']);
            return response(trans_choice('auth.phonenumber_verification_failed', 0), 422);
        }

        if (intval($input['code']) !== intval($session->get('code', 0))) {
            return response(trans_choice('auth.phonenumber_verification_failed_code', 0), 422);
        }

        if (((new \DateTime)->getTimestamp() > intval($session->get('code_expiration_timestamp', 0)))) {
            $session->forget(['code_destination', 'code', 'code_expiration_timestamp']);
            return response(trans_choice('auth.vierfication_code_expired', 0), 422);
        }

        $session->forget(['code_destination', 'code', 'code_expiration_timestamp']);
        $session->put('isPhonenumberVerified', 1);
        $session->put('phonenumberVerifiedAt', (new \DateTime)->getTimestamp());

        return response(trans_choice('auth.phonenumber_verification_successful', 0));
    }

    public function updatePhonenumber(UpdatePhonenumberRequest $request): Response
    {
        $input = $request->safe()->all();
        $session = $request->session();

        if ($session->get('isPhonenumberVerified') === null) {
            $session->forget(['code_destination', 'code', 'code_expiration_timestamp']);
            return response(trans_choice('auth.phonenumber_update_failure', 0));
        }

        if ($session->get('phonenumber') !== strval($input['phonenumber'])) {
            $session->forget(['code_destination', 'code', 'code_expiration_timestamp']);
            return response(trans_choice('auth.phonenumber_update_failure', 0));
        }

        $user = $this->checkAuthentication->getAuthenticated();
        $user->phonenumber = $input['newPhonenumber'];
        $user->saveOrFail();

        $session->forget(['code_destination', 'code', 'code_expiration_timestamp']);
        return response(trans_choice('auth.phonenumber_update_success', 0));
    }

    public function resetPassword(ResetPasswordRequest $request): Response
    {
        $input = $request->safe()->all();
        $session = $request->session();

        if (intval($input['code']) !== intval($session->get('code', 0))) {
            return response(trans_choice('auth.phonenumber_verification_failed_code', 0), 422);
        }

        if ((new \DateTime)->getTimestamp() > intval($session->get('code_expiration_timestamp', 0))) {
            return response(trans_choice('auth.vierfication_code_expired', 0), 422);
        }

        if ($session->get('code_destination') === 'phonenumber') {
            $identifier = 'phonenumber';
        } else {
            $identifier = 'email';
        }

        if (($identifierValue = $session->get($identifier)) === null) {
            return response(trans_choice('auth.vierfication_code_expired', 0), 422);
        }

        $user = $this->databaseRetrieveAccounts->getAccount($this->accountsManagement->resolveUsername($identifierValue));
        $user->password = bcrypt($input['password']);
        $user->saveOrFail();

        $session->forget(['code_destination', 'code', 'code_expiration_timestamp']);
        return response(trans_choice('auth.password_update_success', 0));
    }
}
