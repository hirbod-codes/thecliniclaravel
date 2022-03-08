<?php

namespace Database\dbgatewayes;

use App\Models\Rule;
use App\Models\User;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseCreateAccount;

class DataBaseCreateAccount implements IDataBaseCreateAccount
{
    public function createAccount(array $newUser): DSUser
    {
        if (User::where('firstname', $newUser['firstname'])->where('lastname', $newUser['lastname'])->first() !== null) {
            throw new \RuntimeException('A user with same first name and last name already exists.', 500);
        }

        $ruleId = Rule::where('name', $newUser["rule"])->get()[0]->id;
        unset($newUser["rule"]);

        $newUser['password'] = bcrypt($newUser['password']);

        $newUser[class_basename(Rule::class) . '_' . (new Rule)->getKey()] = $ruleId;

        $user = new User($newUser);

        if (!($user->save())) {
            throw new \RuntimeException("Failed to create new User.", 500);
        } else {
            return $user->getDSUser();
        }
    }
}
