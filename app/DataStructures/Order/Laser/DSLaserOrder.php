<?php

namespace App\DataStructures\Order\Laser;

use App\DataStructures\Order\DSOrder;
use App\DataStructures\Order\DSParts;
use App\DataStructures\Order\DSPackages;
use App\DataStructures\Visit\DSVisits;
use App\DataStructures\Visit\Laser\DSLaserVisits;
use App\DataStructures\Exceptions\Order\InvalidGenderException;
use App\DataStructures\Exceptions\Order\InvalidValueTypeException;

class DSLaserOrder extends DSOrder
{
    private int $priceWithDiscount;

    private DSParts|null $parts;

    private DSPackages|null $packages;

    private string $gender;

    public function __construct(
        int $id,
        int $userId,
        int $priceWithDiscount,
        int $price,
        int $neededTime,
        string $gender,
        \DateTime $createdAt,
        \DateTime $updatedAt,
        DSParts|null $parts = null,
        DSPackages|null $packages = null,
        ?DSVisits $visits = null,
    ) {
        parent::__construct(
            $id,
            $userId,
            $price,
            $neededTime,
            $createdAt,
            $updatedAt,
            $visits,
        );

        if ($parts === null && $packages === null) {
            throw new \RuntimeException("Atleast one of the parts or packages must be provided.", 500);
        }

        $this->setGender($gender);
        $this->setPriceWithDiscount($priceWithDiscount);
        $this->setParts($parts);
        $this->setPackages($packages);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'parts' => $this->parts === null ? null : $this->parts->toArray(),
            'packages' => $this->packages === null ? null : $this->packages->toArray(),
            'priceWithDiscount' => $this->priceWithDiscount,
            'gender' => $this->gender,
        ]);
    }

    protected function validateVisitsType(DSVisits|null $visits): void
    {
        if ($visits === null) {
            return;
        }

        if (!($visits instanceof DSLaserVisits)) {
            throw new InvalidValueTypeException("This data structure only accepts the type: " . DSLaserVisits::class . " as it's associated visits.", 500);
        }
    }

    public function setPriceWithDiscount(int $value): void
    {
        $this->priceWithDiscount = $value;
    }

    public function getPriceWithDiscount(): int
    {
        return $this->priceWithDiscount;
    }

    public function getParts(): DSParts
    {
        return $this->parts;
    }

    public function setParts(DSParts|null $parts): void
    {
        if ((isset($this->packages) && $this->packages->getGender() !== $parts->getGender()) ||
            ($parts->getGender() !== $this->getGender())
        ) {
            throw new InvalidGenderException("Parts gender doesn't match with this data structures' order or package gender.", 500);
        }

        $this->parts = $parts;
    }

    public function getPackages(): DSPackages
    {
        return $this->packages;
    }

    public function setPackages(DSPackages|null $packages): void
    {
        if ((isset($this->parts) && $packages->getGender() !== $this->parts->getGender()) ||
            ($packages->getGender() !== $this->getGender())
        ) {
            throw new InvalidGenderException("Packages gender doesn't match with this data structures' order or part gender.", 500);
        }

        $this->packages = $packages;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setGender(string $gender): void
    {
        $this->gender = $gender;
    }
}
