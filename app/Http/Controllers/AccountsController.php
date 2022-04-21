<?php

namespace App\Http\Controllers;

use App\Auth\CheckAuthentication;
use App\Http\Requests\Accounts\IndexAccountsRequest;
use App\Http\Requests\Accounts\StoreAccountRequest;
use App\Http\Requests\Accounts\UpdateAccountRequest;
use App\Http\Requests\VerifyPhonenumberRequest;
use App\Models\User;
use App\Notifications\SendPhonenumberVerificationCode;
use Database\Interactions\Accounts\DataBaseCreateAccount;
use Database\Interactions\Accounts\DataBaseDeleteAccount;
use Database\Interactions\Accounts\DataBaseRetrieveAccounts;
use Database\Interactions\Accounts\DataBaseUpdateAccount;
use Database\Traits\ResolveUserModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Accounts\AccountsManagement;
use TheClinicUseCases\Accounts\Authentication;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseCreateAccount;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseDeleteAccount;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseRetrieveAccounts;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseUpdateAccount;
use TheClinicUseCases\Privileges\PrivilegesManagement;
use TheClinicUseCases\Exceptions\Accounts\AdminTemptsToDeleteAdminException;
use TheClinicUseCases\Exceptions\Accounts\AdminTemptsToUpdateAdminException;
use TheClinicUseCases\Exceptions\AdminModificationByUserException;
use TheClinicUseCases\Exceptions\AdminsCollisionException;

class AccountsController extends Controller
{
    use ResolveUserModel;

    private CheckAuthentication $checkAuthentication;

    private AccountsManagement $accountsManagement;

    private IDataBaseRetrieveAccounts $dataBaseRetrieveAccounts;

    private IDataBaseCreateAccount $dataBaseCreateAccount;

    private IDataBaseUpdateAccount $dataBaseUpdateAccount;

    private IDataBaseDeleteAccount $dataBaseDeleteAccount;

    public function __construct(
        Authentication|null $authentication = null,
        PrivilegesManagement|null $privilegesManagement = null,
        CheckAuthentication|null $checkAuthentication = null,
        AccountsManagement|null $accountsManagement = null,
        IDataBaseRetrieveAccounts|null $dataBaseRetrieveAccounts = null,
        IDataBaseCreateAccount|null $dataBaseCreateAccount = null,
        IDataBaseUpdateAccount|null $dataBaseUpdateAccount = null,
        IDataBaseDeleteAccount|null $dataBaseDeleteAccount = null,
    ) {
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;
        $this->accountsManagement = $accountsManagement ?: new AccountsManagement($authentication ?: new Authentication, $privilegesManagement ?: new PrivilegesManagement);

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
        $lastAccountId = $validatedInput['lastAccountId'];
        $count = $validatedInput['count'];

        $dsUser = $this->checkAuthentication->getAuthenticatedDSUser();

        $array = array_map(function (DSUser $dsUser) {
            return $dsUser->toArray();
        }, $this->accountsManagement->getAccounts($lastAccountId, $count, $roleName, $dsUser, $this->dataBaseRetrieveAccounts));

        return response()->json($array);
    }

    public function verifyPhonenumber(VerifyPhonenumberRequest $request): Response
    {
        $validatedInput = $request->safe()->all();

        $session = $request->session();
        $session->put('verificationCode', $code = rand(100000, 999999));
        $session->put('phonenumber', $validatedInput['phonenumber']);

        Notification::route('phonenumber', $validatedInput['phonenumber'])
            ->notify(new SendPhonenumberVerificationCode($code))
            //
        ;

        return response(trans_choice('auth.phonenumber_verification_code_sent', 0), 200);
    }

    public function store(StoreAccountRequest $request): Response|JsonResponse
    {
        $validatedInput = $request->safe()->all();

        // Already validated in StoreAccountRequest::class
        unset($validatedInput['code']);
        unset($validatedInput['password_confirmation']);

        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        $username = $this->accountsManagement->createAccount($validatedInput, $dsAuthenticated, $this->dataBaseCreateAccount)->getUsername();

        /** @var \App\Models\Auth\User $newAccount */
        if (($newAccount = User::where('username', '=', $username)->first()) === null) {
            throw new ModelNotFoundException('Failed to find created account!', 404);
        }

        return response()->json($newAccount->authenticatableRole()->getDataStructure()->toArray());
    }

    public function show(string $username): JsonResponse
    {
        $usernameRules = include(base_path() . '/app/Rules/BuiltInRules/Models/User/username.php');
        $validator = Validator::make(['username' => $username], [
            'username' => $usernameRules['username_not_unique'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toArray(), 422);
        }

        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        $dsUser = $this->accountsManagement->getAccount($username, $dsAuthenticated, $this->dataBaseRetrieveAccounts);

        return response()->json($dsUser->toArray());
    }

    public function showSelf(): JsonResponse
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        $dsAuthenticated = $this->accountsManagement->getAccount($dsAuthenticated->getUsername(), $dsAuthenticated, $this->dataBaseRetrieveAccounts);

        return response()->json($dsAuthenticated->toArray());
    }

    public function update(UpdateAccountRequest $request, int $accountId): JsonResponse|Response
    {
        if ($accountId <= 0) {
            return response(__('validation.min.numeric', ['attribute' => 'accountId', 'min' => '1']), 422);
        }

        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        try {
            /** @var User $targetUser*/
            $targetUser = User::query()->whereKey($accountId)->first();

            $dsUpdatedUser = $this->accountsManagement->massUpdateAccount(
                $request->safe()->all(),
                $targetUser->authenticatableRole()->getDataStructure(),
                $dsAuthenticated,
                $this->dataBaseUpdateAccount
            );
        } catch (AdminsCollisionException $e) {
            return response(trans_choice('auth.admin_conflict', 0), 403);
        }

        return response()->json($dsUpdatedUser->toArray());
    }

    public function updateSelf(UpdateAccountRequest $request): JsonResponse|Response
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        try {
            $dsUpdatedAuthenticated = $this->accountsManagement->massUpdateAccount(
                $request->safe()->all(),
                $dsAuthenticated,
                $dsAuthenticated,
                $this->dataBaseUpdateAccount
            );
        } catch (AdminsCollisionException $e) {
            return response(trans_choice('auth.admin_conflict', 0), 403);
        }

        return response()->json($dsUpdatedAuthenticated->toArray());
    }

    public function destroy(int $accountId): Response
    {
        $dsUser = $this->checkAuthentication->getAuthenticatedDSUser();

        try {
            /** @var User $targetUser*/
            $targetUser = User::query()->whereKey($accountId)->first();

            $this->accountsManagement->deleteAccount($targetUser->authenticatableRole()->getDataStructure(), $dsUser, $this->dataBaseDeleteAccount);
        } catch (AdminTemptsToDeleteAdminException $e) {
            return response($e->getMessage(), $e->getCode());
        } catch (AdminModificationByUserException $e) {
            return response($e->getMessage(), $e->getCode());
        }

        return response('The user successfuly deleted.');
    }

    public function destroySelf(): Response
    {
        $dsUser = $this->checkAuthentication->getAuthenticatedDSUser();

        try {
            $this->accountsManagement->deleteAccount($dsUser, $dsUser, $this->dataBaseDeleteAccount);
        } catch (AdminTemptsToDeleteAdminException $e) {
            return response(trans_choice('auth.admin_conflict', 0), 403);
        } catch (AdminModificationByUserException $e) {
            return response(trans_choice('auth.admin_conflict', 0), 403);
        }

        return response('The user successfuly deleted.');
    }
}
