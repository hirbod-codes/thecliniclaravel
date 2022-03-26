<?php

namespace Database\Interactions\Accounts;

use Database\Traits\ResolveUserModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseRetrieveAccounts;

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
        $theModelFullName = $this->resolveRuleModelFullName($ruleName);

        $maxId = $theModelFullName::orderBy('id', 'desc')->first()->id;

        if (!is_null($lastAccountId) && ($lastAccountId > $maxId || $lastAccountId < 1)) {
            throw new \RuntimeException('Invalid last primary key value, it\'s either greater than max primary key or less than 1.', 500);
        }

        $models = $theModelFullName::orderBy((new $theModelFullName)->getKeyName(), 'desc')
            ->where((new $theModelFullName)->getKeyName(), $lastAccountId === null ? '<=' : '<', $lastAccountId === null ? $maxId : $lastAccountId)
            ->take($count)
            ->get();

        $authenticatables = [];
        foreach ($models as $authenticatable) {
            $authenticatables[] = $authenticatable->getDataStructure();
        }

        return $authenticatables;
    }

    public function getAccount(int $id, string $ruleName): DSUser
    {
        $theModelFullName = $this->resolveRuleModelFullName($ruleName);

        if (($authenticatable = $theModelFullName::where((new $theModelFullName)->getKeyName(), $id)->first()) === null) {
            throw new ModelNotFoundException("The user not found.", 404);
        }

        return $authenticatable->getDataStructure();
    }
}
