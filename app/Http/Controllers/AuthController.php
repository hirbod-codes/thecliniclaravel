<?php

namespace App\Http\Controllers;

use App\Auth\CheckAuthentication;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LogInRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\Accounts\SendPhonenumberVerificationCodeRequest;
use App\Http\Requests\ResetPhonenumberRequest;
use App\Http\Requests\VerifyPhonenumberVerificationCodeRequest;
use App\Models\User;
use App\Notifications\SendEmailPasswordResetCode;
use App\Notifications\SendPhonenumberPasswordResetCode;
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
use TheClinicDataStructures\DataStructures\User\DSAdmin;
use TheClinicUseCases\Accounts\AccountsManagement;
use TheClinicUseCases\Accounts\Authentication;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseCreateAccount;
use TheClinicUseCases\Privileges\PrivilegesManagement;

class AuthController extends Controller
{
    private CheckAuthentication|null $checkAuthentication;

    private AccountsManagement|null $accountsManagement;

    private IDataBaseCreateAccount $dataBaseCreateAccount;

    public function __construct(
        Authentication|null $authentication = null,
        PrivilegesManagement|null $privilegesManagement = null,
        CheckAuthentication|null $checkAuthentication = null,
        AccountsManagement|null $accountsManagement = null,
        IDataBaseCreateAccount|null $dataBaseCreateAccount = null,
    ) {
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;
        $this->accountsManagement = $accountsManagement ?: new AccountsManagement($authentication ?: new Authentication, $privilegesManagement ?: new PrivilegesManagement);

        $this->dataBaseCreateAccount = $dataBaseCreateAccount ?: new DataBaseCreateAccount;
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
        $validatedInput = $request->safe()->all();
        $session = $request->session();

        $validatedInput['role'] = 'patient';

        if ($validatedInput['phonenumber'] !== $session->get('phonenumber')) {
            if ($request->header('content-type') === 'application/json') {
                return response()->json(['message' => trans_choice('auth.phonenumber_verification_mismatch', 0)], 422);
            } else {
                return response(trans_choice('auth.phonenumber_verification_mismatch', 0), 422);
            }
        }

        $timestamp = intval($session->get('phonenumber_verification_timestamp', (new \DateTime)->getTimestamp()));
        $validatedInput['phonenumber_verified_at'] = (new \DateTime('now', new \DateTimeZone('UTC')))->setTimestamp($timestamp);

        try {
            $newDSUser = $this->accountsManagement->signupAccount($validatedInput, $this->dataBaseCreateAccount, $this->checkAuthentication);
        } catch (\Throwable $th) {
            if ($th->getCode() === 422) {
                if ($request->header('content-type') === 'application/json') {
                    return response()->json(['error' => $th->getMessage()], $th->getCode());
                } else {
                    return response($th->getMessage(), $th->getCode());
                }
            }
            throw $th;
        }

        $redirecturl = $session->get('redirecturl');
        $session->forget('redirecturl');

        return redirect('/');
    }

    public function forgotPassword(ForgotPasswordRequest $request): Response|JsonResponse
    {
        $validatedInput = $request->safe()->all();
        $session = $request->session();

        $session->forget(['code', 'email', 'phonenumber', 'password_reset_verification_timestamp']);
        $code = rand(100000, 999999);
        if (isset($validatedInput['phonenumber'])) {
            /** @var User $user */
            $user = User::query()->where('phonenumber', '=', $validatedInput['phonenumber'])->firstOrFail();

            $session->put('code', $code);
            $session->put('phonenumber', $validatedInput['phonenumber']);

            $user->notify(new SendPhonenumberPasswordResetCode($code));

            $identifier = 'phonenumber';
        } else {
            /** @var User $user */
            $user = User::query()->where('email', '=', $validatedInput['email'])->where('email_verified_at', '<>', null)->firstOrFail();

            $session->put('code', $code);
            $session->put('email', $validatedInput['email']);

            $user->notify(new SendEmailPasswordResetCode($code));

            $identifier = 'email';
        }

        $session->put('password_reset_verification_timestamp', (new \DateTime)->getTimestamp());

        if ($request->header('content-type') === 'application/json') {
            return response()->json(['message' => trans_choice('auth.' . $identifier . '_verification_code_sent', 0)]);
        } else {
            return response(trans_choice('auth.' . $identifier . '_verification_code_sent', 0));
        }
    }

    public function resetPassword(ResetPasswordRequest $request): Response|JsonResponse
    {
        $validatedInput = $request->safe()->all();
        $validatedInput['code'] = intval($validatedInput['code']);
        $session = $request->session();

        $phonenumber = $session->get('phonenumber');
        $email = $session->get('email');
        $code = intval($session->get('code', 0));

        $future = (new \DateTime)->modify('-1 day')->getTimestamp();
        $password_reset_verification_timestamp = intval($session->get('password_reset_verification_timestamp', $future));

        if ((new \DateTime)->getTimestamp() > (new \DateTime)->setTimestamp(strval($password_reset_verification_timestamp))->modify('+90 seconds')->getTimestamp()) {
            $session->forget(['code', 'email', 'phonenumber', 'password_reset_verification_timestamp']);

            if ($request->header('content-type') === 'application/json') {
                return response()->json(['error' => trans_choice('auth.vierfication_code_expired', 0)], 422);
            } else {
                return response(trans_choice('auth.vierfication_code_expired', 0), 422);
            }
        }

        if (isset($validatedInput['phonenumber']) && ($validatedInput['phonenumber'] !== $phonenumber || $validatedInput['code'] !== $code)) {
            if ($request->header('content-type') === 'application/json') {
                return response()->json(['error' => trans_choice('auth.phonenumber_verification_failed', 0)], 422);
            } else {
                return response(trans_choice('auth.phonenumber_verification_failed', 0), 422);
            }
        } elseif (isset($validatedInput['email']) && ($validatedInput['email'] !== $email || $validatedInput['code'] !== $code)) {
            if ($request->header('content-type') === 'application/json') {
                return response()->json(['error' => trans_choice('auth.email_verification_failed', 0)], 422);
            } else {
                return response(trans_choice('auth.email_verification_failed', 0), 422);
            }
        } elseif ($phonenumber === null && $email === null) {
            if ($request->header('content-type') === 'application/json') {
                return response()->json(['error' => trans_choice('auth.verification_failed', 0)], 422);
            } else {
                return response(trans_choice('auth.verification_failed', 0), 422);
            }
        }
        $session->forget(['code', 'email', 'phonenumber', 'password_reset_verification_timestamp']);

        if (isset($validatedInput['email'])) {
            $user = User::query()->where('email', '=', $validatedInput['email'])->where('email_verified_at', '<>', null)->firstOrFail();
        } elseif (isset($validatedInput['phonenumber'])) {
            $user = User::query()->where('phonenumber', '=', $validatedInput['phonenumber'])->where('phonenumber_verified_at', '<>', null)->firstOrFail();
        }

        $user->password  = bcrypt($validatedInput['password']);

        if (!$user->save()) {
            if ($request->header('content-type') === 'application/json') {
                return response()->json(['message' => trans_choice('auth.password_reset_failed', 0)], 500);
            } else {
                return response(trans_choice('auth.password_reset_failed', 0), 500);
            }
        }

        $redirecturl = $session->get('redirecturl');
        $session->forget('redirecturl');

        if ($request->header('content-type') === 'application/json') {
            return response()->json(['message' => trans_choice('auth.password-reset-successful', 0), 'redirecturl' => $redirecturl ?: '/']);
        } else {
            return response(trans_choice('auth.password-reset-successful', 0), 200);
        }
    }

    public function sendPhonenumberVerificationCode(SendPhonenumberVerificationCodeRequest $request): Response|JsonResponse
    {
        $validatedInput = $request->safe()->all();

        $session = $request->session();

        if (
            ($phonenumber = $session->get('phonenumber', false)) &&
            $phonenumber === $validatedInput['phonenumber'] &&
            ($phonenumber_verification_timestamp = $session->get('phonenumber_verification_timestamp', false)) &&
            (new \DateTime)->getTimestamp() < (new \DateTime)->setTimestamp($phonenumber_verification_timestamp)->modify('+2 minutes')->getTimestamp()
        ) {
            if ($request->header('content-type') === 'application/json') {
                return response()->json(['error' => trans_choice('auth.verification_code_not_expired', 0)], 200);
            } else {
                return response(trans_choice('auth.verification_code_not_expired', 0), 200);
            }
        }

        $session->put('code', $code = rand(100000, 999999));
        $session->put('phonenumber', $validatedInput['phonenumber']);
        $session->put('phonenumber_verification_timestamp', (new \DateTime)->getTimestamp());

        Notification::route('phonenumber', $validatedInput['phonenumber'])
            ->notify(new SendPhonenumberVerificationCode($code));

        if ($request->header('content-type') === 'application/json') {
            return response()->json(['message' => trans_choice('auth.phonenumber_verification_code_sent', 0)], 200);
        } else {
            return response(trans_choice('auth.phonenumber_verification_code_sent', 0), 200);
        }
    }

    public function verifyPhonenumberVerificationCode(VerifyPhonenumberVerificationCodeRequest $request): Response|JsonResponse
    {
        $validatedInput = $request->safe()->all();
        $session = $request->session();

        $validatedInput['code'] = intval($validatedInput['code']);

        $phonenumber = $session->get('phonenumber');
        $code = intval($session->get('code'));

        $future = (new \DateTime)->modify('-1 day')->getTimestamp();
        $phonenumber_verification_timestamp = intval($session->get('phonenumber_verification_timestamp', $future));

        if ((new \DateTime)->getTimestamp() > (new \DateTime)->setTimestamp($phonenumber_verification_timestamp)->modify('+90 seconds')->getTimestamp()) {
            $session->forget(['code', 'phonenumber', 'phonenumber_verification_timestamp']);

            if ($request->header('content-type') === 'application/json') {
                return response()->json(['error' => trans_choice('auth.vierfication_code_expired', 0)], 422);
            } else {
                return response(trans_choice('auth.vierfication_code_expired', 0), 422);
            }
        }

        if ($phonenumber !== $validatedInput['phonenumber'] || $code !== $validatedInput['code']) {
            $session->forget(['code', 'phonenumber', 'phonenumber_verification_timestamp']);

            if ($request->header('content-type') === 'application/json') {
                return response()->json(['error' => trans_choice('auth.phonenumber_verification_failed', 0)], 422);
            } else {
                return response(trans_choice('auth.phonenumber_verification_failed', 0), 422);
            }
        }

        $session->forget(['code']);


        if ($request->header('content-type') === 'application/json') {
            return response()->json(['message' => trans_choice('auth.phonenumber_verification_successful', 0)], 200);
        } else {
            return response(trans_choice('auth.phonenumber_verification_successful', 0), 200);
        }
    }

    public function resetPhonenumber(ResetPhonenumberRequest $request): Response|JsonResponse
    {
        $user = $this->checkAuthentication->getAuthenticated();
        $dsUser = $this->checkAuthentication->getAuthenticatedDSUser();

        $validatedInput = $request->safe()->all();
        $validatedInput['code'] = intval($validatedInput['code']);
        $session = $request->session();

        if (isset($validatedInput['email'])) {
            $targetUser = User::query()->where('email', '=', $validatedInput['email'])->where('email_verified_at', '<>', null)->firstOrFail();
        } elseif (isset($validatedInput['phonenumber'])) {
            $targetUser = User::query()->where('phonenumber', '=', $validatedInput['phonenumber'])->where('phonenumber_verified_at', '<>', null)->firstOrFail();
        }

        if ($user->username !== $targetUser->username || !($dsUser instanceof DSAdmin)) {
            if ($request->header('content-type') === 'application/json') {
                return response()->json(['error' => trans_choice('auth.User-Not-Authorized', 0)], 403);
            } else {
                return response(trans_choice('auth.User-Not-Authorized', 0), 403);
            }
        }

        $phonenumber = $session->get('phonenumber');
        $email = $session->get('email');
        $code = intval($session->get('code', 0));

        $future = (new \DateTime)->modify('-1 day')->getTimestamp();
        $password_reset_verification_timestamp = intval($session->get('password_reset_verification_timestamp', $future));

        if ((new \DateTime)->getTimestamp() > (new \DateTime)->setTimestamp(strval($password_reset_verification_timestamp))->modify('+90 seconds')->getTimestamp()) {
            $session->forget(['code', 'email', 'phonenumber', 'password_reset_verification_timestamp']);

            if ($request->header('content-type') === 'application/json') {
                return response()->json(['error' => trans_choice('auth.vierfication_code_expired', 0)], 422);
            } else {
                return response(trans_choice('auth.vierfication_code_expired', 0), 422);
            }
        }

        if (isset($validatedInput['phonenumber']) && ($validatedInput['phonenumber'] !== $phonenumber || $validatedInput['code'] !== $code)) {
            if ($request->header('content-type') === 'application/json') {
                return response()->json(['error' => trans_choice('auth.phonenumber_verification_failed', 0)], 422);
            } else {
                return response(trans_choice('auth.phonenumber_verification_failed', 0), 422);
            }
        } elseif (isset($validatedInput['email']) && ($validatedInput['email'] !== $email || $validatedInput['code'] !== $code)) {
            if ($request->header('content-type') === 'application/json') {
                return response()->json(['error' => trans_choice('auth.email_verification_failed', 0)], 422);
            } else {
                return response(trans_choice('auth.email_verification_failed', 0), 422);
            }
        } elseif ($phonenumber === null && $email === null) {
            if ($request->header('content-type') === 'application/json') {
                return response()->json(['error' => trans_choice('auth.verification_failed', 0)], 422);
            } else {
                return response(trans_choice('auth.verification_failed', 0), 422);
            }
        }
        $session->forget(['code', 'email', 'phonenumber', 'password_reset_verification_timestamp']);

        $targetUser->phonenumber  = $validatedInput['newPhonenumber'];

        if (!$targetUser->save()) {
            if ($request->header('content-type') === 'application/json') {
                return response()->json(['message' => trans_choice('auth.password_reset_failed', 0)], 500);
            } else {
                return response(trans_choice('auth.password_reset_failed', 0), 500);
            }
        }

        $redirecturl = $session->get('redirecturl');
        $session->forget('redirecturl');

        if ($request->header('content-type') === 'application/json') {
            return response()->json(['message' => trans_choice('auth.password-reset-successful', 0), 'redirecturl' => $redirecturl ?: '/']);
        } else {
            return response(trans_choice('auth.password-reset-successful', 0), 200);
        }
    }
}
