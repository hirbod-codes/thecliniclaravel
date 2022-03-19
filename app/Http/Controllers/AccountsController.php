<?php

namespace App\Http\Controllers;

use App\Auth\CheckAuthentication;
use App\Models\PrivilegeValue;
use Database\Interactions\Accounts\DataBaseCreateAccount;
use Database\Interactions\Accounts\DataBaseDeleteAccount;
use Database\Interactions\Accounts\DataBaseRetrieveAccounts;
use Database\Interactions\Accounts\DataBaseUpdateAccount;
use Database\Traits\ResolveUserModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

class AccountsController extends Controller
{
    use ResolveUserModel;

    private CheckAuthentication|null $checkAuthentication;

    private AccountsManagement|null $accountsManagement;

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
    }

    public function index(string $ruleName, ?int $lastAccountId = null, int $count): JsonResponse
    {
        $dsUser = $this->checkAuthentication->getAuthenticatedDSUser();

        $array = array_map(function (DSUser $dsUser) {
            return $dsUser->toArray();
        }, $this->accountsManagement->getAccounts($lastAccountId, $count, $ruleName, $dsUser, $this->dataBaseRetrieveAccounts));

        return response()->json($array);
    }

    public function create(): JsonResponse
    {
        /** @var \App\Models\Auth\User $authenticated */
        $authenticated = $this->checkAuthentication->getAuthenticated();

        $userPrivileges = [];
        array_map(function (PrivilegeValue $privilegeValue) use (&$userPrivileges) {
            $userPrivileges[$privilegeValue->privilege()->first()->name] = $privilegeValue->privilegeValue;
        }, $authenticated->rule()->first()->privilegeValue()->get()->all());

        return response()->json($userPrivileges);
    }

    public function store(Request $request): JsonResponse
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        $theModelClassFulname = $this->resolveRuleModelFullName($request->rule);

        $username = $this->accountsManagement->createAccount($request->all(), $dsAuthenticated, $this->dataBaseCreateAccount)->getUsername();

        /** @var \App\Models\Auth\User $newAccount */
        if (($newAccount = $theModelClassFulname::where('username', $username)->first()) === null) {
            throw new ModelNotFoundException('Failed to find created account!', 404);
        }

        return response()->json($newAccount->getDataStructure()->toArray());
    }

    public function show(int $accountId, string $ruleName): JsonResponse
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        if ($accountId === $dsAuthenticated->getId()) {
            $dsAuthenticated = $this->accountsManagement->getSelfAccount($ruleName, $dsAuthenticated, $this->dataBaseRetrieveAccounts);
        } else {
            $dsAuthenticated = $this->accountsManagement->getAccount($accountId, $ruleName, $dsAuthenticated, $this->dataBaseRetrieveAccounts);
        }

        $theModelClassFulname = $this->resolveRuleModelFullName($dsAuthenticated->getRuleName());

        $username = $dsAuthenticated->getUsername();

        /** @var \App\Models\Auth\User $newAccount */
        if (($newAccount = $theModelClassFulname::where('username', $username)->first()) === null) {
            throw new ModelNotFoundException('Failed to find created account!', 404);
        }

        return response()->json($newAccount->getDataStructure()->toArray());
    }

    public function edit(int $accountId): JsonResponse
    {
        /** @var \App\Models\Auth\User $authenticated */
        $authenticated = $this->checkAuthentication->getAuthenticated();

        $userPrivileges = [];
        array_map(function (PrivilegeValue $privilegeValue) use (&$userPrivileges) {
            $userPrivileges[$privilegeValue->privilege()->first()->name] = $privilegeValue->privilegeValue;
        }, $authenticated->rule()->first()->privilegeValue()->get()->all());

        return response()->json($userPrivileges);
    }

    public function update(Request $request, int $accountId): JsonResponse|Response
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        try {
            if ($accountId === $dsAuthenticated->getId()) {
                $dsUpdatedAuthenticated = $this->accountsManagement->updateSelfAccount(array_key_first($request->all()), $request->all()[array_key_first($request->all())], $dsAuthenticated, $this->dataBaseUpdateAccount);
            } else {
                $theModelClassFullname = $this->resolveRuleModelFullName($request->rule);
                $targetDSUser = $theModelClassFullname::where((new $theModelClassFullname)->getKeyName(), $accountId)->first()->getDataStructure();

                $dsUpdatedAuthenticated = $this->accountsManagement->updateAccount($request->all(), $targetDSUser, $dsAuthenticated, $this->dataBaseUpdateAccount);
            }
        } catch (AdminTemptsToUpdateAdminException $e) {
            return response($e->getMessage(), $e->getCode());
        }

        $theModelClassFullname = $this->resolveRuleModelFullName($dsUpdatedAuthenticated->getRuleName());

        $username = $dsUpdatedAuthenticated->getUsername();

        /** @var \App\Models\Auth\User $updatedAccount */
        if (($updatedAccount = $theModelClassFullname::where('username', $username)->first()) === null) {
            throw new ModelNotFoundException('Failed to find created account!', 404);
        }

        return response()->json($updatedAccount->getDataStructure()->toArray());
    }

    public function destroy(int $accountId, string $ruleName): Response
    {
        $dsUser = $this->checkAuthentication->getAuthenticatedDSUser();

        try {
            if ($accountId === $dsUser->getId()) {
                $this->accountsManagement->deleteSelfAccount($dsUser, $this->dataBaseDeleteAccount);
            } else {
                $theModelClassFullname = $this->resolveRuleModelFullName($ruleName);
                $targetDSUser = $theModelClassFullname::where((new $theModelClassFullname)->getKeyName(), $accountId)->first()->getDataStructure();

                $this->accountsManagement->deleteAccount($targetDSUser, $dsUser, $this->dataBaseDeleteAccount);
            }
        } catch (AdminTemptsToDeleteAdminException $e) {
            return response($e->getMessage(), $e->getCode());
        }

        return response('The user successfuly deleted.');
    }
}
