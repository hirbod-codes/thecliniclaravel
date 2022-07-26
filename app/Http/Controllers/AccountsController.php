<?php

namespace App\Http\Controllers;

use App\Auth\CheckAuthentication;
use App\Http\Requests\Accounts\IndexAccountsRequest;
use App\Http\Requests\Accounts\isPhonenumberVerificationCodeVerified;
use App\Http\Requests\Accounts\UpdateAccountRequest;
use App\Http\Requests\Accounts\SendPhonenumberVerificationCodeRequest;
use App\Http\Requests\Accounts\StoreAccountRequest;
use App\Http\Requests\Accounts\UpdateSelfAccountRequest;
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
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Accounts\AccountsManagement;
use TheClinicUseCases\Accounts\Authentication;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseCreateAccount;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseDeleteAccount;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseRetrieveAccounts;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseUpdateAccount;
use TheClinicUseCases\Privileges\PrivilegesManagement;
use TheClinicUseCases\Exceptions\Accounts\AdminTemptsToDeleteAdminException;
use TheClinicUseCases\Exceptions\AdminModificationByUserException;
use TheClinicUseCases\Exceptions\AdminsCollisionException;

class AccountsController extends Controller
{
    use ResolveUserModel;

    public const SEPARATOR = '!!!__!!!';

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
        $lastAccountId = isset($validatedInput['lastAccountId']) ? $validatedInput['lastAccountId'] : null;
        $count = $validatedInput['count'];

        $dsUser = $this->checkAuthentication->getAuthenticatedDSUser();

        $array = array_map(function (DSUser $dsUser) {
            return $dsUser->toArray();
        }, $this->accountsManagement->getAccounts($lastAccountId, $count, $roleName, $dsUser, $this->dataBaseRetrieveAccounts));

        return response()->json($array);
    }

    public function sendPhonenumberVerificationCode(SendPhonenumberVerificationCodeRequest $request): JsonResponse
    {
        $validatedInput = $request->safe()->all();
        $code = rand(100000, 999999);
        $id = rand(100000, 999999);

        Notification::route('phonenumber', $validatedInput['phonenumber'])
            ->notify(new SendPhonenumberVerificationCode($code));

        return response()->json([
            'code_created_at_encrypted' => ($t = Crypt::encryptString(strval((new \DateTime)->getTimestamp()) . self::SEPARATOR . strval($id))),
            'code_encrypted' => Crypt::encryptString(strval($code) . self::SEPARATOR . strval($id)),
            'phonenumber_encrypted' => Crypt::encryptString(strval($validatedInput['phonenumber']) . self::SEPARATOR . strval($id)),
            'phonenumber_verified_at_encrypted' => $t,
        ]);
    }

    public function store(StoreAccountRequest $request, string $roleName): Response|JsonResponse
    {
        $validatedInput = $request->safe()->all();

        if (($resposne = $this->verifyPhonenumberVerificationCode(
            $validatedInput['code_created_at_encrypted'],
            $validatedInput['code_encrypted'],
            $validatedInput['code'],
            $validatedInput['phonenumber'],
            $validatedInput['phonenumber_encrypted'],
        ))->getStatusCode() !== 200) {
            return $resposne;
        }

        $validatedInput['role'] = $roleName;

        $timestamp = intval(explode(self::SEPARATOR, Crypt::decryptString($validatedInput['phonenumber_verified_at_encrypted']))[0]);

        $validatedInput['phonenumber_verified_at'] = (new \DateTime('now', new \DateTimeZone('UTC')))->setTimestamp($timestamp);

        // Already validated in StorePatientAccountRequest::class
        unset($validatedInput['code_created_at_encrypted']);
        unset($validatedInput['code_encrypted']);
        unset($validatedInput['code']);
        unset($validatedInput['phonenumber_verified_at_encrypted']);
        unset($validatedInput['phonenumber_encrypted']);
        unset($validatedInput['password_confirmation']);

        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        $username = $this->accountsManagement->createAccount($validatedInput, $dsAuthenticated, $this->dataBaseCreateAccount)->getUsername();

        /** @var \App\Models\Auth\User $newAccount */
        $newAccount = User::query()->where('username', '=', $username)->firstOrFail();

        return response()->json($newAccount->authenticatableRole()->getDataStructure()->toArray());
    }

    public function isPhonenumberVerificationCodeVerified(isPhonenumberVerificationCodeVerified $request): Response
    {
        $validated = $request->safe()->all();

        return $this->verifyPhonenumberVerificationCode($validated['codeCreatedAtEncrypted'], $validated['codeEncrypted'], $validated['code'], $validated['phonenumber'], $validated['phonenumber_encrypted']);
    }

    private function verifyPhonenumberVerificationCode(string $codeCreatedAtEncrypted, string $codeEncrypted, int $code, string $phonenumber, string $phonenumber_encrypted): Response
    {
        $codeCreatedAtDecrypted = intval(explode(self::SEPARATOR, Crypt::decryptString($codeCreatedAtEncrypted))[0]);
        $codeDecrypted = intval(explode(self::SEPARATOR, Crypt::decryptString($codeEncrypted))[0]);
        $phonenumber = intval(explode(self::SEPARATOR, Crypt::decryptString($phonenumber))[0]);
        $phonenumber_encrypted = intval(explode(self::SEPARATOR, Crypt::decryptString($phonenumber_encrypted))[0]);

        $code = intval($code);

        if ((new \DateTime)->getTimestamp() > (new \DateTime)->setTimestamp($codeCreatedAtDecrypted)->modify('+90 seconds')->getTimestamp()) {
            return response()->json(['errors' => ['code' => [trans_choice('auth.vierfication_code_expired', 0)]]], 422);
        }

        if ($code !== $codeDecrypted) {
            return response()->json(['errors' => ['code' => [trans_choice('auth.phonenumber_verification_failed_code', 0)]]], 422);
        }

        if ($phonenumber !== explode(self::SEPARATOR, Crypt::decryptString($phonenumber_encrypted))[0]) {
            return response()->json(['errors' => ['code' => [trans_choice('auth.phonenumber_not_verification', 0)]]], 422);
        }

        return response(trans_choice('auth.phonenumber_verification_successful', 0), 200);
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

        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        $dsUser = $this->accountsManagement->getAccount($username, $dsAuthenticated, $this->dataBaseRetrieveAccounts);

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
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        $dsAuthenticated = $this->accountsManagement->getAccount($dsAuthenticated->getUsername(), $dsAuthenticated, $this->dataBaseRetrieveAccounts);

        return response()->json($dsAuthenticated->toArray());
    }

    public function update(UpdateAccountRequest $request, int $accountId): JsonResponse|Response
    {
        if ($accountId <= 0) {
            return response(__('validation.min.numeric', ['attribute' => 'accountId', 'min' => '1']), 422);
        }

        if (count($input = $request->safe()->all()) === 0) {
            return response(trans_choice('general.no-data', 0), 422);
        }

        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        try {
            /** @var User $targetUser*/
            $targetUser = User::query()->whereKey($accountId)->firstOrFail();

            $dsUpdatedUser = $this->accountsManagement->massUpdateAccount(
                $input,
                $targetUser->authenticatableRole()->getDataStructure(),
                $dsAuthenticated,
                $this->dataBaseUpdateAccount
            );
        } catch (AdminsCollisionException $e) {
            return response(trans_choice('auth.admin_conflict', 0), 403);
        }

        return response()->json($dsUpdatedUser->toArray());
    }

    public function updateSelf(UpdateSelfAccountRequest $request): JsonResponse|Response
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
