<?php

namespace App\Models\rules;

use App\Models\Privilege;
use App\Models\User;
use TheClinicDataStructures\DataStructures\Order\DSOrders;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\DataStructures\User\ICheckAuthentication;
use TheClinicDataStructures\DataStructures\Visit\DSVisits;
use TheClinicDataStructures\Exceptions\DataStructures\User\NoPrivilegeFoundException;

class DSCustom extends DSUser
{
    private string $ruleName;

    private array $customData;

    public function __construct(
        string $ruleName,
        ICheckAuthentication $iCheckAuthentication,
        int $id,
        string $firstname,
        string $lastname,
        string $username,
        string $gender,
        DSVisits|null $visits = null,
        DSOrders|null $orders = null,
        \DateTime $createdAt,
        \DateTime $updatedAt,
    ) {
        $args = func_get_args();
        unset($args['ruleName']);

        parent::__construct(...$args);

        $this->ruleName = $ruleName;
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

    public function getUserPrivileges(): array
    {
        $privileges = [];
        foreach (User::where('id', $this->id)->fisrt()->rule()->get()[0]->privileges()->get() as $privilege) {
            $privileges[$privilege->name] = $privilege->value;
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
