<?php

namespace App\DataStructures\Order;

use App\DataStructures\Interfaces\Arrayable;
use App\DataStructures\Traits\TraitKeyPositioner;
use App\DataStructures\Exceptions\NoKeyFoundException;
use App\DataStructures\Exceptions\Order\InvalidGenderException;
use App\DataStructures\Exceptions\Order\InvalidOffsetTypeException;
use App\DataStructures\Exceptions\Order\InvalidValueTypeException;

class DSPackages implements \Countable, \ArrayAccess, \Iterator, Arrayable, \Stringable
{
    use TraitKeyPositioner;

    /**
     * @var \App\DataStructures\Order\DSPackage[]
     */
    private array $packages = [];

    private string $gender;

    private int $position;

    public function toArray(): array
    {
        return [
            'packages' => array_map(function (DSPackage $package) {
                return $package->toArray();
            }, $this->packages),
            'gender' => $this->gender
        ];
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    // ------------------------------------ \Countable

    public function count(): int
    {
        return count($this->packages);
    }

    // ------------------------------------ \ArrayAccess

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->packages[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (!is_int($offset)) {
            throw new InvalidOffsetTypeException("This data structure only accepts integer as an offset type.", 500);
        }

        return $this->packages[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!is_int($offset) && !is_null($offset)) {
            throw new InvalidOffsetTypeException("This data structure only accepts integer and null as an offset type.", 500);
        }

        if (!($value instanceof DSPackage)) {
            throw new InvalidValueTypeException("This data structure only accepts the type: " . DSPackage::class . " as an array member.", 500);
        }

        if ($value->getGender() !== $this->gender) {
            throw new InvalidGenderException("All the members must have the same gender as this data structure: " . $this->gender . ".", 500);
        }

        if (is_null($offset)) {
            $this->packages[] = $value;
        } elseif (is_int($offset)) {
            $this->packages[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->packages[$offset]);
    }

    // ------------------------------------ \Iterator

    public function current(): mixed
    {
        return $this->packages[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        if (($lastKey = array_key_last($this->packages)) === null) {
            $this->position++;
            return;
        }

        try {
            $this->position = $this->findNextPosition(function ($offset) {
                return isset($this->packages[$offset]);
            }, $this->position, $lastKey);
        } catch (NoKeyFoundException $th) {
            $this->position++;
        }
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->packages[$this->position]);
    }

    // ------------------------------------------------------------------------------------

    /**
     * @param string $gender
     * @param \App\DataStructures\Order\DSPackage[] $packages
     */
    public function __construct(string $gender)
    {
        $this->packages = [];
        $this->gender = $gender;
    }

    public function getGender(): string
    {
        return $this->gender;
    }
}
