<?php

namespace App\UseCases\Accounts;

use App\Models\Auth\User;
use App\UseCases\Accounts\Interfaces\IDataBaseCreateAccount;
use App\UseCases\Accounts\Interfaces\IDataBaseDeleteAccount;
use App\UseCases\Accounts\Interfaces\IDataBaseRetrieveAccounts;
use App\UseCases\Accounts\Interfaces\IDataBaseUpdateAccount;

class AccountsManagement
{
    /**
     * @param integer $lastAccountId
     * @param integer $count
     * @param User $user
     * @param IDataBaseRetrieveAccounts $db
     * @return \App\Models\User[]
     */
    public function getAccounts(int $count, string $ruleName, IDataBaseRetrieveAccounts $db, int $lastAccountId = null): array
    {
        return $db->getAccounts($count, $ruleName, $lastAccountId);
    }

    public function getAccount(string $targetUserUsername, IDataBaseRetrieveAccounts $db): User
    {
        $targetUser = $db->getAccount($targetUserUsername);

        return $targetUser;
    }

    public function createAccount(array $input, IDataBaseCreateAccount $db): User
    {
        return $db->createAccount($input);
    }

    public function signupAccount(array $input, IDataBaseCreateAccount $db): User
    {
        return $db->createAccount($input);
    }

    public function deleteAccount(User $targetUser, IDataBaseDeleteAccount $db): void
    {
        $db->deleteAccount($targetUser);
    }

    public function massUpdateAccount(array $input, User $targetUser, IDataBaseUpdateAccount $db): User
    {
        if (count($input) === 0) {
            throw new \InvalidArgumentException('$input is empty', 500);
        }

        return $db->massUpdateAccount($input, $targetUser);
    }

    public function updateAccount(string $attribute, mixed $newValue, User $targetUser, IDataBaseUpdateAccount $db): User
    {
        return $db->updateAccount($attribute, $newValue, $targetUser);
    }
}
