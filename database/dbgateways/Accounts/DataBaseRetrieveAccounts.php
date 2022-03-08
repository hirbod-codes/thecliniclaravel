<?php

namespace Database\dbgatewayes;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseRetrieveAccounts;

class DataBaseRetrieveAccounts implements IDataBaseRetrieveAccounts
{
    /**
     * @return \TheClinic\DataStructures\User\DSUser[]
     */
    public function getAccounts(?int $lastVisitId = null, int $count): array
    {
        $maxId = User::orderBy('id', 'desc')->first()->id;

        $users = [];
        /** @var \App\Models\User $user */
        foreach (User::orderBy('id', 'desc')->where('id', '<', $lastVisitId ?: $maxId)->take($count)->get() as $user) {
            $users[] = $user->getDSUser();
        }

        return $users;
    }

    public function getAccount(int $Id): DSUser
    {
        if (($user = User::where('id', $Id)->first()) === null) {
            throw new ModelNotFoundException("The user not found.", 404);
        }

        return $user->getDSUser();
    }
}
