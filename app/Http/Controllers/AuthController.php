<?php

namespace App\Http\Controllers;

use App\Auth\CheckAuthentication;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Database\Interactions\Accounts\DataBaseCreateAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\TokenRepository;
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

    public function logout(): Response
    {
        $tokenId = $this->getTokenId();

        $tokenRepository = app(TokenRepository::class);
        $tokenRepository->revokeAccessToken($tokenId);

        $refreshTokenRepository = app(RefreshTokenRepository::class);
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($tokenId);

        Artisan::call('passport:purge');

        return response('Successfully loged you out.', 200);
    }

    private function getTokenId(): int
    {
        return (new CheckAuthentication)->getAuthenticated()->token()->id;
    }

    public function register(RegisterUserRequest $request): Response|JsonResponse
    {
        $validatedInput = $request->safe()->all();
        unset($validatedInput['code']);

        $validatedInput['role'] = 'patient';

        $newDSUser = $this->accountsManagement->signupAccount($validatedInput, $this->dataBaseCreateAccount, $this->checkAuthentication);

        $newUser = User::query()
            ->where('username', '=', $newDSUser->getUsername())
            ->firstOrFail();

        return response()->json($newUser->toArray());
    }
}
