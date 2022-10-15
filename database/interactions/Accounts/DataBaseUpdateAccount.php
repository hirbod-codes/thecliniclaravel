<?php

namespace Database\Interactions\Accounts;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\UseCases\Accounts\Interfaces\IDataBaseUpdateAccount;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;

class DataBaseUpdateAccount implements IDataBaseUpdateAccount
{
    public function massUpdateAccount(array $input, User $user): User
    {
        $authenticatable = $user->authenticatableRole();

        try {
            DB::beginTransaction();

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

            $targetId = intval(array_reverse(explode('/', Request::path()))[0]);
            $targetUser = User::query()->whereKey($targetId)->firstOrFail();
            $modelFullName = get_class($targetUser->authenticatableRole);

            $authAattributes = [];
            foreach (Schema::getColumnListing((new $modelFullName)->getTable()) as $column) {
                if (array_search($column, array_keys($input)) !== false) {
                    $authAattributes[$column] = $input[$column];
                }
            }

            if (!(isset($userAattributes) && $user->update($userAattributes)) || !(isset($authAattributes) && $authenticatable->update($authAattributes))) {
                throw new \RuntimeException('', 500);
            }

            DB::commit();
            return $user->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function updateAccount(string $attribute, mixed $newValue, User $user): User
    {
        if (!$user->update([$attribute => $newValue])) {
            throw new \RuntimeException('', 500);
        }

        return $user->refresh();
    }
}
