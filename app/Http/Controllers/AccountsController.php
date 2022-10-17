<?php

namespace App\Http\Controllers;

use App\Auth\CheckAuthentication;
use App\Http\Requests\Accounts\AccountsCountRequest;
use App\Http\Requests\Accounts\IndexAccountsRequest;
use App\Http\Requests\Accounts\isPhonenumberVerificationCodeVerified;
use App\Http\Requests\Accounts\UpdateAccountRequest;
use App\Http\Requests\Accounts\SendPhonenumberVerificationCodeRequest;
use App\Http\Requests\Accounts\StoreAccountRequest;
use App\Models\RoleName;
use App\Models\User;
use App\Notifications\SendPhonenumberVerificationCode;
use Database\Interactions\Accounts\DataBaseCreateAccount;
use Database\Interactions\Accounts\DataBaseDeleteAccount;
use Database\Interactions\Accounts\DataBaseRetrieveAccounts;
use Database\Interactions\Accounts\DataBaseUpdateAccount;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Database\Interactions\Accounts\AccountsManagement;
use Database\Interactions\Accounts\Interfaces\IDataBaseCreateAccount;
use Database\Interactions\Accounts\Interfaces\IDataBaseDeleteAccount;
use Database\Interactions\Accounts\Interfaces\IDataBaseRetrieveAccounts;
use Database\Interactions\Accounts\Interfaces\IDataBaseUpdateAccount;
use Database\Interactions\Exceptions\Accounts\AdminTemptsToDeleteAdminException;
use Database\Interactions\Exceptions\AdminModificationByUserException;
use Database\Interactions\Exceptions\AdminsCollisionException;

class AccountsController extends Controller
{
    public const SEPARATOR = '!!!__!!!';

    private CheckAuthentication $checkAuthentication;

    private AccountsManagement $accountsManagement;

    private IDataBaseRetrieveAccounts $dataBaseRetrieveAccounts;

    private IDataBaseCreateAccount $dataBaseCreateAccount;

    private IDataBaseUpdateAccount $dataBaseUpdateAccount;

    private IDataBaseDeleteAccount $dataBaseDeleteAccount;

    public function __construct(
        CheckAuthentication|null $checkAuthentication = null,
        AccountsManagement|null $accountsManagement = null,
        IDataBaseRetrieveAccounts|null $dataBaseRetrieveAccounts = null,
        IDataBaseCreateAccount|null $dataBaseCreateAccount = null,
        IDataBaseUpdateAccount|null $dataBaseUpdateAccount = null,
        IDataBaseDeleteAccount|null $dataBaseDeleteAccount = null,
    ) {
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;
        $this->accountsManagement = $accountsManagement ?: new AccountsManagement();

        $this->dataBaseRetrieveAccounts = $dataBaseRetrieveAccounts ?: new DataBaseRetrieveAccounts;
        $this->dataBaseCreateAccount = $dataBaseCreateAccount ?: new DataBaseCreateAccount;
        $this->dataBaseUpdateAccount = $dataBaseUpdateAccount ?: new DataBaseUpdateAccount;
        $this->dataBaseDeleteAccount = $dataBaseDeleteAccount ?: new DataBaseDeleteAccount;

        parent::__construct();
    }

    public function index(IndexAccountsRequest $request): JsonResponse
    {
        $validatedInput = $request->safe()->all();
        $roleName = $validatedInput['roleName'];
        $lastAccountId = isset($validatedInput['lastAccountId']) ? $validatedInput['lastAccountId'] : null;
        $count = $validatedInput['count'];

        return response()->json($this->dataBaseRetrieveAccounts->getAccounts($count, $roleName, $lastAccountId));
    }

    public function accountsCount(AccountsCountRequest $request): Response
    {
        $input = $request->safe()->all();
        return response($this->dataBaseRetrieveAccounts->getAccountsCount($input['roleName']));
    }

    public function store(StoreAccountRequest $request, string $roleName): Response|JsonResponse
    {
        $validatedInput = $request->safe()->all();
        $session = $request->session();

        if ($session->get('isPhonenumberVerified') === null || $session->get('phonenumber') !== $validatedInput['phonenumber']) {
            return response(trans_choice('auth.phonenumber_not_verification', 0), 422);
        }

        $validatedInput['role'] = $roleName;

        $timestamp = intval($session->get('phonenumberVerifiedAt', 0));
        if ($timestamp === 0) {
            throw new \LogicException('!!!', 500);
        }

        $validatedInput['phonenumber_verified_at'] = (new \DateTime('now', new \DateTimeZone('UTC')))->setTimestamp($timestamp);

        $user = $this->dataBaseCreateAccount->createAccount($validatedInput);

        return response()->json($user->toArray());
    }

    public function show(string $placeholder): JsonResponse
    {
        $validator = Validator::make(['placeholder' => $placeholder], [
            'placeholder' => ['string', 'required'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toArray(), 422);
        }

        $username = $this->accountsManagement->resolveUsername($placeholder);

        $user = $this->dataBaseRetrieveAccounts->getAccount($username);

        return response()->json($user->toArray());
    }

    public function showSelf(): JsonResponse
    {
        $user = $this->checkAuthentication->getAuthenticated();

        // In order to have a unified data transmission
        $user = $this->dataBaseRetrieveAccounts->getAccount($user->username);

        return response()->json($user->toArray());
    }

    public function update(UpdateAccountRequest $request, int $accountId): JsonResponse
    {
        if ($accountId <= 0) {
            return response()->json([
                'errors' => ['accountId' => [__('validation.min.numeric', ['attribute' => 'accountId', 'min' => '1'])]],
                'message' => __('validation.min.numeric', ['attribute' => 'accountId', 'min' => '1'])
            ], 422);
        }

        if (count($input = $request->safe()->all()) === 0) {
            return response()->json([
                'errors' => [],
                'message' => trans_choice('general.no-data', 0)
            ], 422);
        }

        $targetUser = $this->dataBaseRetrieveAccounts->getAccount($this->accountsManagement->resolveUsername($accountId));

        $updatedUser = $this->dataBaseUpdateAccount->massUpdateAccount(
            $input,
            $targetUser
        );

        return response()->json($updatedUser->toArray());
    }

    public function destroy(int $accountId): Response|JsonResponse
    {
        if ($accountId <= 0) {
            return response()->json([
                'errors' => ['accountId' => [__('validation.min.numeric', ['attribute' => 'accountId', 'min' => '1'])]],
                'message' => __('validation.min.numeric', ['attribute' => 'accountId', 'min' => '1'])
            ], 422);
        }

        $targetUser = $this->dataBaseRetrieveAccounts->getAccount($this->accountsManagement->resolveUsername($accountId));

        $this->dataBaseDeleteAccount->deleteAccount($targetUser);

        return response('The user successfuly deleted.');
    }
}
