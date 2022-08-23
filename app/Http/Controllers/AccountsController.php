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
use App\UseCases\Accounts\AccountsManagement;
use App\UseCases\Accounts\Interfaces\IDataBaseCreateAccount;
use App\UseCases\Accounts\Interfaces\IDataBaseDeleteAccount;
use App\UseCases\Accounts\Interfaces\IDataBaseRetrieveAccounts;
use App\UseCases\Accounts\Interfaces\IDataBaseUpdateAccount;
use App\UseCases\Exceptions\Accounts\AdminTemptsToDeleteAdminException;
use App\UseCases\Exceptions\AdminModificationByUserException;
use App\UseCases\Exceptions\AdminsCollisionException;

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
        $validatedInput = $request->all();
        $roleName = $validatedInput['roleName'];
        $lastAccountId = isset($validatedInput['lastAccountId']) ? $validatedInput['lastAccountId'] : null;
        $count = $validatedInput['count'];

        return response()->json($this->accountsManagement->getAccounts($count, $roleName, $this->dataBaseRetrieveAccounts, $lastAccountId));
    }

    public function accountsCount(AccountsCountRequest $request): Response
    {
        $input = $request->safe()->all();
        $roleName = RoleName::query()->where('name', '=', $input['roleName'])->firstOrFail();
        return response(count($roleName->childRoleModel->userType));
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

        $user = $this->accountsManagement->createAccount($validatedInput, $this->dataBaseCreateAccount);

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

        $username = $this->findUsername($placeholder);
        if (gettype($username) === 'object' && get_class($username) === JsonResponse::class) {
            return $username;
        }

        $dsUser = $this->accountsManagement->getAccount($username, $this->dataBaseRetrieveAccounts);

        return response()->json($dsUser->toArray());
    }

    private function findUsername(string $placeholder): JsonResponse|string|null
    {
        if (($user = User::query()
            ->where('username', '=', $placeholder)
            ->first()) !== null) {
            return $placeholder;
        }

        if (($user = User::query()
            ->where('email', '=', $placeholder)
            ->first()) !== null) {
            return $placeholder;
        }

        if (($user = User::query()
            ->where('phonenumber', '=', $placeholder)
            ->first()) !== null) {
            return $placeholder;
        }

        if (Str::contains($placeholder, '-')) {
            $firstname = explode('-', $placeholder)[0];
            $lastname = explode('-', $placeholder)[1];

            $firstnameRules = array_merge((include(base_path() . '/app/Rules/BuiltInRules/Models/User/firstname.php'))['firstname_optional'], ['required_with:lastname']);
            $lastnameRules = array_merge((include(base_path() . '/app/Rules/BuiltInRules/Models/User/lastname.php'))['lastname_optional'], ['required_with:firstname']);

            $validator = Validator::make(['firstname' => $firstname, 'lastname' => $lastname], [
                'firstname' => $firstnameRules,
                'lastname' => $lastnameRules,
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->toArray()], 422);
            }

            if (($user = User::query()
                ->where('firstname', '=', $firstname)
                ->where('lastname', '=', $lastname)
                ->first()) === null) {
                return $user->username;
            }
        }

        throw new ModelNotFoundException('', 404);
    }

    public function showSelf(): JsonResponse
    {
        $user = $this->checkAuthentication->getAuthenticated();

        $user = $this->accountsManagement->getAccount($user->username, $this->dataBaseRetrieveAccounts);

        return response()->json($user->toArray());
    }

    public function update(UpdateAccountRequest $request, int $accountId): JsonResponse|Response
    {
        if ($accountId <= 0) {
            return response(__('validation.min.numeric', ['attribute' => 'accountId', 'min' => '1']), 422);
        }

        if (count($input = $request->safe()->all()) === 0) {
            return response(trans_choice('general.no-data', 0), 422);
        }

        try {
            /** @var User $targetUser*/
            $targetUser = User::query()->whereKey($accountId)->firstOrFail();

            $dsUpdatedUser = $this->accountsManagement->massUpdateAccount(
                $input,
                $targetUser,
                $this->dataBaseUpdateAccount
            );
        } catch (AdminsCollisionException $e) {
            return response(trans_choice('auth.admin_conflict', 0), 403);
        }

        return response()->json($dsUpdatedUser->toArray());
    }

    public function destroy(int $accountId): Response
    {
        try {
            /** @var User $targetUser*/
            $targetUser = User::query()->whereKey($accountId)->first();

            $this->accountsManagement->deleteAccount($targetUser, $this->dataBaseDeleteAccount);
        } catch (AdminTemptsToDeleteAdminException $e) {
            return response($e->getMessage(), $e->getCode());
        } catch (AdminModificationByUserException $e) {
            return response($e->getMessage(), $e->getCode());
        }

        return response('The user successfuly deleted.');
    }
}
