<?php

namespace App\Models\roles;

use App\Models\Privilege;
use App\Models\PrivilegeValue;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use TheClinicDataStructures\DataStructures\User\DSUser;
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
        try {
            return $this->getPrivilegModel($privilege)->toArray()['value'];
        } catch (\Throwable $th) {
            throw new NoPrivilegeFoundException();
        }
    }

    public static function getUserPrivileges(): array
    {
        $privileges = [];

        /** @var PrivilegeValue $privilegeValue */
        foreach (Role::query()->where('name', '=', self::$roleName)->first()->privilegeValues as $privilegeValue) {
            $privileges[$privilegeValue->privilege->name] = $privilegeValue->value;
        }

        return $privileges;
    }

    public function setPrivilege(string $privilege, mixed $value): void
    {
        $privilegeModel = $this->getPrivilegModel($privilege)->privilegeValues;
        $privilegeModel->value = strval($value);

        if (!$privilegeModel->save()) {
            throw new \LogicException("Failed to set the privilege!", 500);
        }
    }

    public function privilegeExists(string $privilege): bool
    {
        $privileges = $this->getPrivileges();

        foreach ($privileges as $p) {
            if ($p === $privilege) {
                return true;
            }
        }

        return false;
    }

    public function getPrivilegModel(string $privilege): Privilege
    {
        foreach (User::query()->whereKey($this->id)->fisrt()->rule->privilegeValues as $privilegeValue) {
            if (($privilegeModel = $privilegeValue->privilege)->name === $privilege) {
                return $privilegeModel;
            }
        }

        throw new ModelNotFoundException();
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
