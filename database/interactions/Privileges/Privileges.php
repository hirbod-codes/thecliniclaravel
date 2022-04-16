<?php

namespace Database\Interactions\Privileges;

use App\Models\PrivilegeValue;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\DataStructures\User\Interfaces\IPrivilege;

class Privileges implements IPrivilege
{
    public function setPrivilege(DSUser $user, string $privilege, mixed $value): void
    {
        /** @var User $user */
        $user = User::query()
            ->whereKey($user->getId())
            ->first();

        if ($user === null) {
            throw new ModelNotFoundException('', 404);
        }

        /** @var PrivilegeValue $privilegeValue */
        foreach ($user->role->privilegeValues as $privilegeValue) {
            if ($privilegeValue->privilege->name !== $privilege) {
                continue;
            }

            $privilegeValue->privilegeValue = $privilegeValue->convertPrivilegeValueToString($value);

            if (!$privilegeValue->save()) {
                throw new \RuntimeException('Failed to update privilege model.', 500);
            }

            break;
        }
    }
}
