<?php

namespace App\DataStructures\Order;

use App\DataStructures\Interfaces\Arrayable;

class DSPart implements Arrayable, \Stringable
{
    private int $id;

    private string $name;

    private string $gender;

    private int $price;

    private int $neededTime;

    private \DateTime $createdAt;

    private \DateTime $updatedAt;

    public function __construct(
        int $id,
        string $name,
        string $gender,
        int $price,
        int $neededTime,
        \DateTime $createdAt,
        \DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->gender = $gender;
        $this->price = $price;
        $this->neededTime = $neededTime;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'gender' => $this->gender,
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

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $var): void
    {
        $this->id = $var;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($var): void
    {
        $this->name = $var;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setGender(string $var): void
    {
        $this->gender = $var;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $var): void
    {
        $this->price = $var;
    }

    public function getNeededTime(): int
    {
        return $this->neededTime;
    }

    public function setNeededTime(int $var): void
    {
        $this->neededTime = $var;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $var): void
    {
        $this->createdAt = $var;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $var): void
    {
        $this->updatedAt = $var;
    }
}
