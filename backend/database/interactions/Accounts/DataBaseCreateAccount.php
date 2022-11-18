<?php

namespace Database\Interactions\Accounts;

use App\Helpers\TraitAuthResolver;
use App\Http\Controllers\AccountDocumentsController;
use App\Models\Auth\User as Authenticatable;
use App\Models\RoleName;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Database\Interactions\Accounts\Interfaces\IDataBaseCreateAccount;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;

class DataBaseCreateAccount implements IDataBaseCreateAccount
{
    use TraitAuthResolver;

    private AccountDocumentsController $accountDocumentsController;

    public function __construct(
        null|AccountDocumentsController $accountDocumentsController = null,
    ) {
        $this->accountDocumentsController = $accountDocumentsController ?: new AccountDocumentsController;
    }

    public function createAccount(string $userType, string $roleName, array $input, array $specialInput, null|string|File|UploadedFile $avatar = null): User
    {
        try {
            DB::beginTransaction();

            $userModel = new User();
            foreach ($input as $key => $value) {
                $userModel->{$key} = $key === 'password' ? bcrypt($value) : $value;
            }

            $userModel->saveOrFail();

            $userTypeModelFullname = $this->resolveAuthModelFullName($userType);
            $childRoleModel = RoleName::query()->where('name', '=', $roleName)->firstOrFail()->childRoleModel;

            /** @var Authenticatable $authModel */
            $authModel = new $userTypeModelFullname;
            foreach ($specialInput as $key => $value) {
                $authModel->{$key} = $value;
            }
            $authModel->{(new User)->getForeignKey()} = $userModel->getKey();
            $authModel->{$childRoleModel->getForeignKey()} = $childRoleModel->getKey();
            $authModel->saveOrFail();

            if (!is_null($avatar)) {
                $this->accountDocumentsController->makeAvatar($avatar, $userModel->getKey() . '.jpg', 'private');
            }

            DB::commit();

            $userModel->authenticatableRole;

            Event::dispatch(new Registered($userModel));

            return $userModel;
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
