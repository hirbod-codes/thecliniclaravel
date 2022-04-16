<?php

namespace App\Models\roles;

use App\Models\Privilege;
use App\Models\PrivilegeValue;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\DataStructures\User\Interfaces\IPrivilege;
use TheClinicDataStructures\Exceptions\DataStructures\User\NoPrivilegeFoundException;

class DSCustom extends DSUser
{
    private string $roleName;

    private array $customData;

    public function __construct(...$args)
    {
        $this->roleName = $args['roleName'];
        unset($args['roleName']);

        parent::__construct(...$args);
    }

    public function setData(array $data): static
    {
        $this->customData = $data;

        return $this;
    }

    public function getRuleName(): string
    {
        return $this->roleName;
    }

    public function getPrivilege(string $privilege): mixed
    {
        if (
            ($role = Role::query()
                ->where('name', '=', $this->roleName)
                ->first()
            ) !== null &&
            ($privilege = Privilege::query()
                ->where('name', '=', $privilege)
                ->first()
            ) !== null &&
            ($privilegeValue = PrivilegeValue::query()
                ->where($role->getForeignKey(), '=', $role->getKey())
                ->where($privilege->getForeignKey(), '=', $privilege->getKey())
                ->first()
            ) !== null
        ) {
            return $privilegeValue->privilegeValue;
        } else {
            throw new ModelNotFoundException('Failed to find the role model.', 404);
        }
    }

    public function privilegeExists(string $privilege): bool
    {
        /**
         * @var Role $role
         * @var PrivilegeValue[] $privilegeValues
         */
        if (($privilege = Privilege::query()->where('name', '=', $privilege)->first()) === null ||
            count($privilegeValues = ($role = Role::query()->where('name', '=', $this->roleName)->first())->privilegeValues) === 0
        ) {
            throw new ModelNotFoundException('Failed to find privilege values.', 500);
        }

        foreach ($privilegeValues as $privilegeValue) {
            if ($privilegeValue->{$privilege->getForeignKey()} === $privilege->getKey()) {
                return true;
            }
        }

        return false;
    }

    public static function getUserPrivileges(string $roleName = ""): array
    {
        $privileges = [];

        /** @var PrivilegeValue $privilegeValue */
        foreach (Role::query()->where('name', '=', $roleName)->first()->privilegeValues as $privilegeValue) {
            $privileges[$privilegeValue->privilege->name] = $privilegeValue->value;
        }

        return $privileges;
    }

    public function setPrivilege(string $privilege, mixed $value, IPrivilege $ip): void
    {
        $ip->setPrivilege($this, $privilege, $value);
    }

    public function __get(string $attribute)
    {
        if (in_array($attribute, array_keys($this->customData))) {
            return $this->customData[$attribute];
        }

        if ((new \ReflectionProperty(static::class, $attribute))->isPublic()) {
            return $this->{$attribute};
        }

        throw new \RuntimeException("Undefined Property: " . $attribute, 500);
    }
}
