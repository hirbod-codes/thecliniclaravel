<?php

namespace Database\dbgatewayes;

use App\Models\User;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseDeleteAccount;

class DataBaseDeleteAccount implements IDataBaseDeleteAccount
{
    public function deleteAccount(DSUser $user): void
    {
        User::where('id', $user->getId())->first()->delete();
    }
}
