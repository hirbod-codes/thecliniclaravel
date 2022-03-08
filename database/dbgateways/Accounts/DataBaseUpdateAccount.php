<?php

namespace Database\dbgatewayes;

use App\Models\Rule;
use App\Models\User;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseUpdateAccount;

class DataBaseUpdateAccount implements IDataBaseUpdateAccount
{
    public function massUpdateAccount(array $input, DSUser $user): DSUser
    {
        if (isset($input['firstname']) || isset($input['lastname'])) {
            if (!isset($input['firstname'])) {
                $input['firstname'] = User::where('id', $user->getId())->first()->firstname;
            } elseif (!isset($input['lastname'])) {
                $input['lastname'] = User::where('id', $user->getId())->first()->lastname;
            }
            if (User::where('firstname', $input['firstname'])->where('lastname', $input['lastname'])->first() !== null) {
                throw new \RuntimeException('A user with same first name and last name already exists.', 500);
            }
        }

        if (isset($input['passwrod'])) {
            $input['password'] = bcrypt($input['password']);
        }

        if (isset($input['rule'])) {
            $ruleId = Rule::where('name', $input["rule"])->get()[0]->id;
            unset($input["rule"]);
            $input[class_basename(Rule::class) . '_' . (new Rule)->getKey()] = $ruleId;
        }

        if (User::where('id', $user->getId())->first()->update($input)) {
            return User::where('id', $user->getId())->first()->getDSUser();
        } else {
            throw new \RuntimeException('Failed to update the user.', 500);
        }
    }

    public function updateAccount(string $attribute, mixed $newValue, DSUser $user): DSUser
    {
        $user = User::where('id', $user->getId())->first();

        $user->{$attribute} = $newValue;

        if (!$user->save()) {
            throw new \RuntimeException('Failed to update user\'s ' . $attribute . '.', 500);
        } else {
            return $user->getDSUser();
        }
    }
}
