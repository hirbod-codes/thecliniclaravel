<?php

namespace App\Http\Controllers;

use App\Auth\CheckAuthentication;
use App\Http\Requests\Accounts\AccountsCountRequest;
use App\Http\Requests\Accounts\IndexAccountsRequest;
use App\Http\Requests\Accounts\UpdateAccountRequest;
use App\Http\Requests\Accounts\StoreAdminRequest;
use App\Http\Requests\Accounts\StoreDoctorRequest;
use App\Http\Requests\Accounts\StoreSecretaryRequest;
use App\Http\Requests\Accounts\StoreOperatorRequest;
use App\Http\Requests\Accounts\StorePatientRequest;
use Database\Interactions\Accounts\DataBaseCreateAccount;
use Database\Interactions\Accounts\DataBaseDeleteAccount;
use Database\Interactions\Accounts\DataBaseRetrieveAccounts;
use Database\Interactions\Accounts\DataBaseUpdateAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Database\Interactions\Accounts\AccountsManagement;
use Database\Interactions\Accounts\Interfaces\IDataBaseCreateAccount;
use Database\Interactions\Accounts\Interfaces\IDataBaseDeleteAccount;
use Database\Interactions\Accounts\Interfaces\IDataBaseRetrieveAccounts;
use Database\Interactions\Accounts\Interfaces\IDataBaseUpdateAccount;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

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

    public function store(Session $session, string $userType, string $roleName, array $userAttributes, array $userAccountAttributes, string|File|UploadedFile|null $avatar = null): Response|JsonResponse
    {
        if ($session->get('isPhonenumberVerified') === null || $session->get('phonenumber') !== $userAttributes['phonenumber']) {
            return response(trans_choice('auth.phonenumber_not_verification', 0), 422);
        }

        $timestamp = intval($session->get('phonenumberVerifiedAt', 0));
        if ($timestamp === 0) {
            throw new \LogicException('!!!', 500);
        }

        $userAttributes['phonenumber_verified_at'] = (new \DateTime('now', new \DateTimeZone('UTC')))->setTimestamp($timestamp);

        $user = $this->dataBaseCreateAccount->createAccount($userType, $roleName, $userAttributes, $userAccountAttributes, isset($avatar) ? $avatar : null);

        return response()->json($user->toArray());
    }

    public function storeAdmin(StoreAdminRequest $request, string $roleName): Response|JsonResponse
    {
        $validatedInput = $request->safe()->all();

        return $this->store($request->session(), 'admin', $roleName, $validatedInput['userAttributes'], $validatedInput['userAccountAttributes'], isset($validatedInput['avatar']) ? $validatedInput['avatar'] : null);
    }

    public function storeDoctor(StoreDoctorRequest $request, string $roleName): Response|JsonResponse
    {
        $validatedInput = $request->safe()->all();

        return $this->store($request->session(), 'doctor', $roleName, $validatedInput['userAttributes'], $validatedInput['userAccountAttributes'], isset($validatedInput['avatar']) ? $validatedInput['avatar'] : null);
    }

    public function storeSecretary(StoreSecretaryRequest $request, string $roleName): Response|JsonResponse
    {
        $validatedInput = $request->safe()->all();

        return $this->store($request->session(), 'secretary', $roleName, $validatedInput['userAttributes'], $validatedInput['userAccountAttributes'], isset($validatedInput['avatar']) ? $validatedInput['avatar'] : null);
    }

    public function storeOperator(StoreOperatorRequest $request, string $roleName): Response|JsonResponse
    {
        $validatedInput = $request->safe()->all();

        return $this->store($request->session(), 'operator', $roleName, $validatedInput['userAttributes'], $validatedInput['userAccountAttributes'], isset($validatedInput['avatar']) ? $validatedInput['avatar'] : null);
    }

    public function storePatient(StorePatientRequest $request, string $roleName): Response|JsonResponse
    {
        $validatedInput = $request->safe()->all();

        return $this->store($request->session(), 'patient', $roleName, $validatedInput['userAttributes'], $validatedInput['userAccountAttributes'], isset($validatedInput['avatar']) ? $validatedInput['avatar'] : null);
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

        $input = $request->safe()->all();

        $targetUser = $this->dataBaseRetrieveAccounts->getAccount($this->accountsManagement->resolveUsername((int)$accountId));

        $updatedUser = $this->dataBaseUpdateAccount->massUpdateAccount($input['userAttributes'], $input['userAccountAttributes'], $targetUser);

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
