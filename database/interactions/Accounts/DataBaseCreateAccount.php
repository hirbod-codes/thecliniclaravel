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
                throw new \RuntimeException('A user with same first name and last name already exists.', 422);
            }

            $userAattributes = [];
            foreach (Schema::getColumnListing((new User)->getTable()) as $column) {
                if (array_search($column, array_keys($input)) !== false) {
                    if ($column === 'password') {
                        $userAattributes[$column] = hash('sha256', $input[$column]);
                        continue;
                    }
                    $userAattributes[$column] = $input[$column];
                }
            }

            $ruleName = $input["role"];
            unset($input["role"]);

            if (isset($input['avatar'])) {
                $avatar = $input['avatar'];
                unset($input["avatar"]);
            }


            $userModel = new User;
            $userModel->{(new Role)->getForeignKeyForName()} = Role::where('name', $ruleName)->first()->name;
            if (!$userModel->fill($userAattributes)->save()) {
                DB::rollback();
                throw new \RuntimeException('Failed to create the account.', 500);
            }

            $modelFullName = $this->resolveRuleModelFullName($ruleName);

            /** @var Authenticatable $roleModel */
            $roleModel = new $modelFullName;
            $roleModel->{(new $modelFullName)->getKeyName()} = $userModel->{(new User)->getKeyName()};
            $roleModel->{$roleModel->getUserRoleNameFKColumnName()} = $userModel->{(new Role)->getForeignKeyForName()};
            if (!$roleModel->fill($input)->save()) {
                DB::rollback();
                throw new \RuntimeException('Failed to create the account.', 500);
            }

            if (isset($avatar)) {
                (new AccountDocumentsController)->makeAvatar($avatar, $input['accountId'], 'local');
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
