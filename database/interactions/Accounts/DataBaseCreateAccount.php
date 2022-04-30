<?php

namespace Database\Interactions\Accounts;

use App\Http\Controllers\AccountDocumentsController;
use App\Models\Auth\User as Authenticatable;
use App\Models\Role;
use App\Models\User;
use Database\Traits\ResolveUserModel;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseCreateAccount;
use Illuminate\Support\Facades\Schema;

class DataBaseCreateAccount implements IDataBaseCreateAccount
{
    use ResolveUserModel;

    public function createAccount(array $input): DSUser
    {
        try {
            DB::beginTransaction();

            if (User::where('firstname', $input['firstname'])->where('lastname', $input['lastname'])->first() !== null) {
                throw new \RuntimeException(trans_choice('auth.duplicate_fullname', 0), 422);
            }

            $userAattributes = [];
            foreach (Schema::getColumnListing((new User)->getTable()) as $column) {
                if (array_search($column, array_keys($input)) !== false) {
                    if ($column === 'password') {
                        $userAattributes[$column] = bcrypt($input[$column]);
                        continue;
                    }

                    $userAattributes[$column] = $input[$column];
                }
            }

            $ruleName = $input["role"];
            unset($input["role"]);

            $modelFullName = $this->resolveRuleModelFullName($ruleName);

            $authAattributes = [];
            foreach (Schema::getColumnListing((new $modelFullName)->getTable()) as $column) {
                if (array_search($column, array_keys($input)) !== false) {
                    $authAattributes[$column] = $input[$column];
                }
            }

            if (isset($input['avatar'])) {
                $avatar = $input['avatar'];
                unset($input["avatar"]);
            }


            $userModel = new User;
            $userModel->{(new Role)->getForeignKeyForName()} = Role::where('name', $ruleName)->first()->name;

            foreach ($userAattributes as $key => $value) {
                $userModel->{$key} = $value;
            }

            $userModel->saveOrFail();

            /** @var Authenticatable $roleModel */
            $roleModel = new $modelFullName;
            $roleModel->{(new $modelFullName)->getKeyName()} = $userModel->{(new User)->getKeyName()};
            $roleModel->{$roleModel->getUserRoleNameFKColumnName()} = $userModel->{(new Role)->getForeignKeyForName()};

            foreach ($authAattributes as $key => $value) {
                $roleModel->{$key} = $value;
            }

            $roleModel->saveOrFail();

            if (isset($avatar)) {
                (new AccountDocumentsController)->makeAvatar($avatar, $userModel->getKey(), 'private');
            }

            DB::commit();

            event(new Registered($userModel));

            return $roleModel->getDataStructure();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
