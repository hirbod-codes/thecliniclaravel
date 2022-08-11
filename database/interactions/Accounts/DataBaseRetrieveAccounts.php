<?php

namespace Database\Interactions\Accounts;

use App\Models\Model;
use App\Models\roles\AdminRole;
use App\Models\User;
use Database\Traits\ResolveUserModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseRetrieveAccounts;
use App\UseCases\Accounts\Interfaces\IDataBaseRetrieveAccounts;

class DataBaseRetrieveAccounts implements IDataBaseRetrieveAccounts
{
    use ResolveUserModel;

    /**
     * @param integer|null $lastAccountId
     * @param integer $count
     * @param string $ruleName
     * @return \TheClinic\DataStructures\User\DSUser[]
     */
    public function getAccounts(int $count, string $ruleName, ?int $lastAccountId = null): array
    {
        /** @var Model $theModelFullName */
        $theModelFullName = $this->resolveRuleModelFullName($ruleName);

        $maxId = $theModelFullName::orderBy('id', 'desc')->first()->id;

        if (!is_null($lastAccountId) && ($lastAccountId > $maxId || $lastAccountId < 1)) {
            throw new \RuntimeException('Invalid last primary key value, it\'s either greater than max primary key or less than 1.', 500);
        }

        $models = $theModelFullName::query()
            ->orderBy((new $theModelFullName)->getKeyName(), 'desc')
            ->where((new AdminRole)->getUserRoleNameFKColumnName(), '=', $ruleName)
            ->where((new $theModelFullName)->getKeyName(), $lastAccountId === null ? '<=' : '<', $lastAccountId === null ? $maxId : $lastAccountId)
            ->take($count)
            ->get();

        $authenticatables = [];
        foreach ($models as $authenticatable) {
            $authenticatables[] = $authenticatable->getDataStructure();
        }

        return $authenticatables;
    }

    public function getAccount(string $targetUserUsername): DSUser
    {
        /** @var User $user */
        if (($user = User::query()->where('username', $targetUserUsername)->first()) === null) {
            throw new ModelNotFoundException("The user not found.", 404);
        }

        return $user->authenticatableRole()->getDataStructure();
    }
}
