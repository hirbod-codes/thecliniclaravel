<?php

namespace Database\Interactions\Accounts;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Helpers\TraitRoleResolver;
use App\Models\RoleName;
use App\UseCases\Accounts\Interfaces\IDataBaseRetrieveAccounts;

class DataBaseRetrieveAccounts implements IDataBaseRetrieveAccounts
{
    use TraitRoleResolver;

    public function getAccountsCount(string $roleName): int
    {
        $roleName = RoleName::query()->where('name', '=', $roleName)->firstOrFail();

        return count($roleName->childRoleModel->userType);
    }

    /**
     * @param integer|null $lastAccountId
     * @param integer $count
     * @param string $roleName
     * @return array [ [ 'columns' => [...], 'rows' => [...] ], ... ]
     */
    public function getAccounts(int $count, string $roleName, ?int $lastAccountId = null): array
    {
        $childRoleModel = RoleName::query()->where('name', '=', $roleName)->firstOrFail()->childRoleModel;
        $userTypeModelFullname = $childRoleModel->getUserTypeModelFullname();

        $lastUser = User::query()->orderBy((new User)->getKeyName(), 'desc')->first();
        if ($lastUser === null) {
            $maxId = 0;
        } else {
            $maxId = $lastUser->getKey();
        }

        if (!is_null($lastAccountId) && ($lastAccountId > $maxId || $lastAccountId < 1)) {
            throw new \RuntimeException('Invalid last primary key value, it\'s either greater than max primary key or less than 1.', 500);
        }

        $users = $userTypeModelFullname::query()
            ->orderBy((new User)->getForeignKey(), 'desc')
            ->where((new $childRoleModel)->getForeignKey(), '=', $childRoleModel->getKey())
            ->where((new User)->getForeignKey(), $lastAccountId === null ? '<=' : '<', $lastAccountId === null ? $maxId : $lastAccountId)
            ->take($count)
            ->with('user')
            ->get()
            //
        ;

        return $users->toArray();
    }

    public function getAccount(string $targetUserUsername): User
    {
        return User::query()->where('username', $targetUserUsername)->firstOrFail();
    }
}
