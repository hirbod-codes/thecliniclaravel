<?php

namespace Database\Interactions\Accounts;

use App\Models\User;
use Database\Traits\ResolveUserModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseUpdateAccount;

class DataBaseUpdateAccount implements IDataBaseUpdateAccount
{
    use ResolveUserModel;

    public function massUpdateAccount(array $input, DSUser $user): DSUser
    {
        $theModelClassFullName = $this->resolveRuleModelFullName($this->resolveRuleName($user));
        $theModelClassPrimaryKey = (new $theModelClassFullName)->getKeyName();

        if (isset($input['firstname']) || isset($input['lastname'])) {
            if (!isset($input['firstname'])) {
                $input['firstname'] = $theModelClassFullName::where($theModelClassPrimaryKey, $user->getId())->first()->firstname;
            } elseif (!isset($input['lastname'])) {
                $input['lastname'] = $theModelClassFullName::where($theModelClassPrimaryKey, $user->getId())->first()->lastname;
            }

            if (User::where('firstname', $input['firstname'])->where('lastname', $input['lastname'])->first() !== null) {
                throw new \RuntimeException('A user with same first name and last name already exists.', 500);
            }
        }

        $userInput = [];
        foreach (Schema::getColumnListing((new User)->getTable()) as $column) {
            if (array_search($column, array_keys($input)) !== false) {
                $userInput[$column] = $input[$column];
                unset($input[$column]);
            }
        }
        $roleInput = $input;

        try {
            DB::beginTransaction();

            if (
                User::where('username', $user->getUsername())->first()->update($userInput) &&
                $theModelClassFullName::where($theModelClassPrimaryKey, $user->getId())->first()->update($roleInput)
            ) {
                DB::commit();
                return $theModelClassFullName::where($theModelClassPrimaryKey, $user->getId())->first()->getDataStructure();
            } else {
                throw new \RuntimeException('Failed to update the user.', 500);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function updateAccount(string $attribute, mixed $newValue, DSUser $user): DSUser
    {
        $authenticatable = User::where('username', $user->getUsername())->first();

        if (array_search($attribute, Schema::getColumnListing((new User)->getTable())) !== false) {
            if ($attribute === 'firstname' || $attribute === 'lastname') {
                $firstname = $authenticatable->firstname;
                $lastname = $authenticatable->lastname;

                if ($attribute === 'firstname') {
                    $firstname = $newValue;
                } else {
                    $lastname = $newValue;
                }

                if (User::where('firstname', $firstname)->where('lastname', $lastname)->first() !== null) {
                    throw new \RuntimeException('A user with same first name and last name already exists.', 500);
                }
            }

            $authenticatable->{$attribute} = $newValue;
        } else {
            $authenticatable = $authenticatable->authenticatableRole();

            $authenticatable->{$attribute} = $newValue;
        }

        if (!$authenticatable->save()) {
            throw new \RuntimeException('Failed to update user\'s ' . $attribute . '.', 500);
        } elseif ($authenticatable instanceof User) {
            return $authenticatable->authenticatableRole()->getDataStructure();
        } else {
            return $authenticatable->getDataStructure();
        }
    }
}
