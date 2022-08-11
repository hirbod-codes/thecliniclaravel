<?php

namespace App\DataStructures\Order;

use App\DataStructures\Interfaces\Arrayable;
use App\DataStructures\Traits\TraitKeyPositioner;
use App\DataStructures\Exceptions\NoKeyFoundException;
use App\DataStructures\Exceptions\Order\InvalidGenderException;
use App\DataStructures\Exceptions\Order\InvalidOffsetTypeException;
use App\DataStructures\Exceptions\Order\InvalidValueTypeException;

class DSParts implements \Countable, \ArrayAccess, \Iterator, Arrayable, \Stringable
{
    use TraitKeyPositioner;

    private string $gender;

    /**
     * @var \App\DataStructures\Order\DSPart[]
     */
    private array $parts = [];

    private int $position;

    public function toArray(): array
    {
        return [
            'gender' => $this->gender,
            'parts' => array_map(function (DSPart $part) {
                return $part->toArray();
            }, $this->parts)
        ];
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    // ------------------------------------ \Countable

    public function count(): int
    {
        return count($this->parts);
    }

    // ------------------------------------ \ArrayAccess

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->parts[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (!is_int($offset)) {
            throw new InvalidOffsetTypeException("This data structure only accepts integer as an offset type.", 500);
        }

        return $this->parts[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!is_int($offset) && !is_null($offset)) {
            throw new InvalidOffsetTypeException("This data structure only accepts integer and null as an offset type.", 500);
        }

        if (!($value instanceof DSPart)) {
            throw new InvalidValueTypeException("This data structure only accepts the type: " . DSPart::class . " as an array member.", 500);
        }

        if ($value->getGender() !== $this->gender) {
            throw new InvalidGenderException("All the members must have the same gender as this data structure: " . $this->gender . ".", 500);
        }

        if (is_null($offset)) {
            $this->parts[] = $value;
        } elseif (is_int($offset)) {
            $this->parts[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->parts[$offset]);
    }

    // ------------------------------------ \Iterator

    public function current(): mixed
    {
        return $this->parts[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        if (($lastKey = array_key_last($this->parts)) === null) {
            $this->position++;
            return;
        }

        try {
            $this->position = $this->findNextPosition(function ($offset) {
                return isset($this->parts[$offset]);
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
        return isset($this->parts[$this->position]);
    }

    // ------------------------------------------------------------------------------------

    /**
     * Constructs a new instance.
     *
     * @param string $gender
     * @param \App\DataStructures\Order\DSPart[] $parts
     */
    public function __construct(string $gender)
    {
        $this->gender = $gender;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setGender(string $var): void
    {
        $this->gender = $var;
    }
}
