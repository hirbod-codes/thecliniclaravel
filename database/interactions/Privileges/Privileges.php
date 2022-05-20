<?php

namespace Database\Interactions\Privileges;

use App\Models\PrivilegeValue;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\DataStructures\User\Interfaces\IPrivilege;

class Privileges implements IPrivilege
{
    /**
     * @return array<string, string> ['privilege' => value, ...]
     */
    public function getUserPrivileges(DSUser $dsUser): array
    {
        /** @var User $user */
        $privilegeValues = ($user = User::query()->where('username', '=', $dsUser->getUsername())->firstOrFail())->role->privilegeValues;

        $array = [];
        /** @var PrivilegeValue $privilegeValue */
        foreach ($privilegeValues as $privilegeValue) {
            $array[$privilegeValue->privilegeValue] = $privilegeValue->privilege->name;
        }

        return $array;
    }
}
