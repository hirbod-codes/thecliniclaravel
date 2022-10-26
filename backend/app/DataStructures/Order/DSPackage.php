<?php

namespace App\DataStructures\Order;

use App\DataStructures\Interfaces\Arrayable;
use App\DataStructures\Order\DSParts;
use App\DataStructures\Exceptions\Order\InvalidGenderException;

class DSPackage implements Arrayable, \Stringable
{
    private int $id;

    private string $name;

    private string $gender;

    private int $price;

    private DSParts $parts;

    private \DateTime $createdAt;

    private \DateTime $updatedAt;

    public function __construct(
        int $id,
        string $name,
        string $gender,
        int $price,
        DSParts $parts,
        \DateTime $createdAt,
        \DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->gender = $gender;
        $this->price = $price;
        $this->setParts($parts);
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
            'parts' => $this->parts->toArray(),
            'createdAt' => $this->createdAt->format("Y-m-d H:i:s"),
            'updatedAt' => $this->updatedAt->format("Y-m-d H:i:s")
        ];
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    public function getParts(): DSParts
    {
        return $this->parts;
    }

    public function setParts(DSParts $parts): void
    {
        if ($parts->getGender() !== $this->getGender()) {
            throw new InvalidGenderException("Parts genders must be same as this package's gender.", 500);
        }

        $this->parts = $parts;
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
