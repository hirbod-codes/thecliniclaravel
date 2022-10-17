<?php

namespace Database\Interactions\Accounts;

use App\Helpers\TraitRoleResolver;
use App\Http\Controllers\AccountDocumentsController;
use App\Models\Auth\User as Authenticatable;
use App\Models\RoleName;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Database\Interactions\Accounts\Interfaces\IDataBaseCreateAccount;
use Illuminate\Support\Facades\Schema;

class DataBaseCreateAccount implements IDataBaseCreateAccount
{
    /**
     * @param array $input ['typeAttributes*' => [], 'userAttributes*' => [], 'role*' => 'admin', 'avatar' => '']
     * @return \App\Models\User
     */
    public function createAccount(array $input): User
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

            $modelFullName = ($childRoleModel = RoleName::query()->where('name', '=', $ruleName)->firstOrFail()->childRoleModel)->getUserTypeModelFullname();

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

            $userModel = new User();
            foreach ($userAattributes as $key => $value) {
                $userModel->{$key} = $value;
            }

            $userModel->saveOrFail();

            /** @var Authenticatable $roleModel */
            $roleModel = new $modelFullName;
            foreach ($authAattributes as $key => $value) {
                $roleModel->{$key} = $value;
            }
            $roleModel->{(new User)->getForeignKey()} = $userModel->getKey();
            $roleModel->{$childRoleModel->getForeignKey()} = $childRoleModel->getKey();
            $roleModel->saveOrFail();

            if (isset($avatar)) {
                (new AccountDocumentsController)->makeAvatar($avatar, $userModel->getKey() . '.jpg', 'private');
            }

            DB::commit();

            $userModel->authenticatableRole;

            event(new Registered($userModel));

            return $userModel;
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
