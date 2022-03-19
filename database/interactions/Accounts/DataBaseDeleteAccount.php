<?php

namespace Database\Interactions\Accounts;

use Database\Traits\ResolveUserModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Accounts\Interfaces\IDataBaseDeleteAccount;

class DataBaseDeleteAccount implements IDataBaseDeleteAccount
{
    use ResolveUserModel;

    public function deleteAccount(DSUser $user): void
    {
        $theModelClassFullName = $this->resolveRuleModelFullName(strtolower(str_replace('DS', '', class_basename(get_class($user)))));

        $theModel = $theModelClassFullName::where((new $theModelClassFullName)->getKeyName(), $user->getId())->first();

        if ($theModel === null) {
            throw new ModelNotFoundException('Failed to find the user.', 404);
        }

        $theModel->delete();
    }
}
