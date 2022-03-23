<?php

namespace App\Models\roles;

use App\Models\Privilege;
use App\Models\Rule;
use App\Models\User;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\Exceptions\DataStructures\User\NoPrivilegeFoundException;

class DSCustom extends DSUser
{
    private string $ruleName;

    private array $customData;

    public function __construct(...$args)
    {
        $this->ruleName = $args['ruleName'];
        unset($args['ruleName']);

        parent::__construct(...$args);
    }

    public function setData(array $data): static
    {
        $this->customData = $data;

        return $this;
    }

    public function getRuleName(): string
    {
        return $this->ruleName;
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

        foreach (Rule::where('name', 'custom')->first()->privilegeValue()->get() as $privilegeValue) {
            $privileges[$privilegeValue->privilege()->first()->name] = $privilegeValue->value;
        }

        return $privileges;
    }

    public function setPrivilege(string $privilege, mixed $value): void
    {
        $privilegeModel = $this->getPrivilegModel($privilege);
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
        return User::where('id', $this->id)->fisrt()->rule()->privileges()->where('name', $privilege)->get()[0];
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
