<?php

namespace Database\Interactions\Accounts;

use App\Models\Auth\User as Authenticatable;
use App\Models\Role;
use App\Models\User;
use Database\Traits\ResolveUserModel;
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
                throw new \RuntimeException('A user with same first name and last name already exists.', 500);
            }

            $userAattributes = [];
            foreach (Schema::getColumnListing((new User)->getTable()) as $column) {
                if (array_search($column, array_keys($input)) !== false) {
                    $userAattributes[$column] = $input[$column];
                }
            }

            $ruleName = $input["role"];
            unset($input["role"]);

            $userModel = new User;
            $userModel->{(new Role)->getForeignKey()} = Role::where('name', $ruleName)->first()->name;
            if (!$userModel->fill($userAattributes)->save()) {
                DB::rollback();
                throw new \RuntimeException('Failed to create the account.', 500);
            }

            $modelFullName = $this->resolveRuleModelFullName($ruleName);

            /** @var Authenticatable $roleModel */
            $roleModel = new $modelFullName;
            $roleModel->{(new User)->getForeignKey()} = $userModel->{(new User)->getKeyName()};
            $roleModel->{$roleModel->getUserRoleNameFKColumnName()} = $userModel->{(new Role)->getForeignKey()};
            if (!$roleModel->fill($input)->save()) {
                DB::rollback();
                throw new \RuntimeException('Failed to create the account.', 500);
            }

            DB::commit();

            return $roleModel->getDataStructure();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
