<?php

namespace Database\Interactions\Accounts;

use Database\Traits\ResolveUserModel;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseUpdateAccount;

class DataBaseUpdateAccount implements IDataBaseUpdateAccount
{
    use ResolveUserModel;

    public function massUpdateAccount(array $input, DSUser $user): DSUser
    {
        $theModelClassFullName = $this->resolveRuleModelFullName(strtolower(str_replace('DS', '', class_basename(get_class($user)))));
        $theModelClassPrimaryKey = (new $theModelClassFullName)->getKeyName();

        // Add to validation
        if (isset($input['firstname']) || isset($input['lastname'])) {
            if (!isset($input['firstname'])) {
                $input['firstname'] = $theModelClassFullName::where($theModelClassPrimaryKey, $user->getId())->first()->firstname;
            } elseif (!isset($input['lastname'])) {
                $input['lastname'] = $theModelClassFullName::where($theModelClassPrimaryKey, $user->getId())->first()->lastname;
            }

            if ($theModelClassFullName::where('firstname', $input['firstname'])->where('lastname', $input['lastname'])->first() !== null) {
                throw new \RuntimeException('A user with same first name and last name already exists.', 500);
            }
        }

        if ($theModelClassFullName::where($theModelClassPrimaryKey, $user->getId())->first()->update($input)) {
            return $theModelClassFullName::where($theModelClassPrimaryKey, $user->getId())->first()->getDataStructure();
        } else {
            throw new \RuntimeException('Failed to update the user.', 500);
        }
    }

    public function updateAccount(string $attribute, mixed $newValue, DSUser $user): DSUser
    {
        $theModelClassFullName = $this->resolveRuleModelFullName(strtolower(str_replace('DS', '', class_basename(get_class($user)))));
        $theModelClassPrimaryKey = (new $theModelClassFullName)->getKeyName();

        $authenticatable = $theModelClassFullName::where($theModelClassPrimaryKey, $user->getId())->first();

        // Add to validation
        if ($attribute === 'firstname' || $attribute === 'lastname') {
            $firstname = $authenticatable->firstname;
            $lastname = $authenticatable->lastname;

            if ($attribute === 'firstname') {
                $firstname = $newValue;
            } else {
                $lastname = $newValue;
            }

            if ($theModelClassFullName::where('firstname', $firstname)->where('lastname', $lastname)->first() !== null) {
                throw new \RuntimeException('A user with same first name and last name already exists.', 500);
            }
        }

        $authenticatable->{$attribute} = $newValue;

        if (!$authenticatable->save()) {
            throw new \RuntimeException('Failed to update user\'s ' . $attribute . '.', 500);
        } else {
            return $authenticatable->getDataStructure();
        }
    }
}
