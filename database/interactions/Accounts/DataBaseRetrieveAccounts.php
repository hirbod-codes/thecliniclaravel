<?php

namespace Database\Interactions\Accounts;

use App\Models\User;
use App\Helpers\TraitAuthResolver;
use App\Models\Auth\User as AuthUser;
use App\Models\Model;
use App\Models\RoleName;
use Database\Interactions\Accounts\Interfaces\IDataBaseRetrieveAccounts;
use Illuminate\Database\Eloquent\Collection;

class DataBaseRetrieveAccounts implements IDataBaseRetrieveAccounts
{
    use TraitAuthResolver;

    public function getAccountsCount(string $roleName): int
    {
        $childRoleModel = RoleName::query()->where('name', '=', $roleName)->firstOrFail()->childRoleModel;

        $count = 0;
        /** @var AuthUser $userTypeModelFullname */
        foreach ($this->authModelsFullName() as $userTypeModelFullname) {
            $count += $userTypeModelFullname::query()
                ->where((new $childRoleModel)->getForeignKey(), '=', $childRoleModel->getKey())
                ->count()
                //
            ;
        }

        return $count;
    }

    /**
     * @param integer|null $lastAccountId
     * @param integer $count
     * @param string $roleName
     * @return Collection<int, App\Models\Auth\User>
     */
    public function getAccounts(int $count, string $roleName, ?int $lastAccountId = null): Collection
    {
        $roleNameModel = RoleName::query()->where('name', '=', $roleName)->firstOrFail();

        $lastUser = User::query()->orderBy((new User)->getKeyName(), 'desc')->first();
        if ($lastUser === null) {
            $maxId = 0;
        } else {
            $maxId = $lastUser->getKey();
        }

        if (!is_null($lastAccountId) && ($lastAccountId > $maxId || $lastAccountId < 1)) {
            throw new \RuntimeException('Invalid last primary key value, it\'s either greater than max primary key or less than 1.', 500);
        }

        $users = new Collection();

        /** @var AuthUser $userTypeModelFullname */
        foreach ($this->authModelsFullName() as $userTypeModelFullname) {
            /** @var Model $roleModel */
            $roleModelFullName = (new $userTypeModelFullname)->getRoleModelFullname();
            $roleModelFK = (new $roleModelFullName)->getForeignKey();

            $roleModel = $roleModelFullName::query()->where((new RoleName)->getForeignKey(), '=', $roleNameModel->getKey())->first();

            if ($roleModel === null) {
                continue;
            }

            $temp = $userTypeModelFullname::query()
                ->orderBy((new User)->getForeignKey(), 'desc')
                ->where($roleModelFK, '=', $roleModel->getKey())
                ->where((new User)->getForeignKey(), $lastAccountId === null ? '<=' : '<', $lastAccountId === null ? $maxId : $lastAccountId)
                ->take($count)
                ->with('user')
                ->get()
                //
            ;

            $users = $users->concat($temp->all());
        }

        return $users;
    }

    public function getAccount(string $targetUserUsername): User
    {
        return User::query()->where('username', $targetUserUsername)->firstOrFail();
    }
}
