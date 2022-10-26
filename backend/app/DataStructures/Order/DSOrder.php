<?php

namespace App\DataStructures\Order;

use App\DataStructures\Interfaces\Arrayable;
use App\DataStructures\Visit\DSVisits;
use App\DataStructures\Exceptions\Order\InvalidValueTypeException;

abstract class DSOrder implements Arrayable, \Stringable
{
    protected int $id;

    protected int $userId;

    protected int $price;

    protected int $neededTime;

    protected DSVisits|null $visits;

    protected \DateTime $createdAt;

    protected \DateTime $updatedAt;

    public function __construct(
        int $id,
        int $userId,
        int $price,
        int $neededTime,
        \DateTime $createdAt,
        \DateTime $updatedAt,
        ?DSVisits $visits = null
    ) {
        $this->id = $id;
        $this->userId = $userId;

        $this->validateVisitsType($visits);

        $this->visits = $visits;
        $this->price = $price;
        $this->neededTime = $neededTime;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'visits' => $this->visits === null ? null : $this->visits->toArray(),
            'price' => $this->price,
            'neededTime' => $this->neededTime,
            'createdAt' => $this->createdAt->format("Y-m-d H:i:s"),
            'updatedAt' => $this->updatedAt->format("Y-m-d H:i:s")
        ];
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    protected function validateVisitsType(DSVisits|null $visits): void
    {
        if ($visits === null) {
            return;
        }

        if (!($visits instanceof DSVisits)) {
            throw new InvalidValueTypeException("This data structure only accepts the type: " . DSVisits::class . " as it's associated visits.", 500);
        }
    }

    public function getVisits(): DSVisits|null
    {
        return $this->visits;
    }

    public function setVisits(DSVisits|null $visits): void
    {
        $this->validateVisitsType($visits);
        $this->visits = $visits;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    public function getNeededTime(): int
    {
        return $this->neededTime;
    }

    public function setNeededTime(int $neededTime): void
    {
        $this->neededTime = $neededTime;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
