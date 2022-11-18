<?php

namespace App\DataStructures\Time;

use App\DataStructures\Interfaces\IClonable;
use App\DataStructures\Time\DSDownTime;
use App\DataStructures\Interfaces\Arrayable;
use App\DataStructures\Traits\TraitKeyPositioner;
use App\DataStructures\Exceptions\NoKeyFoundException;
use App\DataStructures\Exceptions\Time\DublicateDownTimeNameException;
use App\DataStructures\Exceptions\Time\InvalidOffsetTypeException;
use App\DataStructures\Exceptions\Time\InvalidValueTypeException;
use App\DataStructures\Exceptions\Time\TimeSequenceViolationException;

class DSDownTimes implements
    \ArrayAccess,
    \Iterator,
    \Countable,
    \Stringable,
    IClonable,
    Arrayable
{
    use TraitKeyPositioner;

    /**
     * @var \App\DataStructures\Time\DSDownTime[]
     */
    private array $dsDownTimes;

    /**
     * position of the pointer of this data structure.(as we use it as a Iterable object)
     *
     * @var integer
     */
    private int $position;

    public function __construct()
    {
        $this->dsDownTimes = [];
        $this->position = 0;
    }

    public function cloneIt(): self
    {
        $newDSDownTimes = new DSDownTimes();

        foreach ($this->dsDownTimes as $dsDownTime) {
            $newDSDownTimes[] = $dsDownTime->cloneIt();
        }

        return $newDSDownTimes;
    }

    public function toArray(): array
    {
        return array_map(function (DSDownTime $dsDownTime) {
            return $dsDownTime->toArray();
        }, $this->dsDownTimes);
    }

    public static function toObject(array $resultOfToArrayMethod): self
    {
        $dsDowTimes = new static;

        foreach ($resultOfToArrayMethod as $dowTime) {
            $dsDowTime = DSDownTime::toObject($dowTime);
            $dsDowTimes[] = $dsDowTime;
        }

        return $dsDowTimes;
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    // ------------------------------------ \ArrayAccess

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->dsDownTimes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->dsDownTimes[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!($value instanceof DSDownTime)) {
            throw new InvalidValueTypeException("The new member must be an object of class: " . DSDownTime::class, 500);
        }

        foreach ($this->dsDownTimes as $dsDownTime) {
            if ($dsDownTime->getName() === $value->getName()) {
                throw new DublicateDownTimeNameException('', 500);
            }
        }

        if (is_null($offset)) {
            if (
                (count($this->dsDownTimes) === 0)
                || (count($this->dsDownTimes) !== 0 &&
                    $this->dsDownTimes[array_key_last($this->dsDownTimes)]->getEndTimestamp() < $value->getStartTimestamp())
            ) {
                $this->dsDownTimes[] = $value;
                return;
            }
        } elseif (gettype($offset) === "integer") {
            try {
                $previousKey = $this->findPreviousPosition([$this, "offsetExists"], $offset);
            } catch (NoKeyFoundException $th) {
            }

            try {
                if (($lastKey = array_key_last($this->dsDownTimes)) !== null) {
                    $nextKey = $this->findNextPosition([$this, "offsetExists"], $offset, $lastKey);
                }
            } catch (NoKeyFoundException $th) {
            }

            if (isset($previousKey) && isset($nextKey)) {
                if (
                    $this->dsDownTimes[$previousKey]->getEndTimestamp() < $value->getStartTimestamp()
                    &&
                    $this->dsDownTimes[$nextKey]->getStartTimestamp() > $value->getEndTimestamp()
                ) {
                    $this->dsDownTimes[$offset] = $value;
                    return;
                }
            } elseif (isset($previousKey)) {
                if ($this->dsDownTimes[$previousKey]->getEndTimestamp() < $value->getStartTimestamp()) {
                    $this->dsDownTimes[$offset] = $value;
                    return;
                }
            } elseif (isset($nextKey)) {
                if ($this->dsDownTimes[$nextKey]->getStartTimestamp() > $value->getEndTimestamp()) {
                    $this->dsDownTimes[$offset] = $value;
                    return;
                }
            } else {
                $this->dsDownTimes[$offset] = $value;
                return;
            }
        } else {
            throw new InvalidOffsetTypeException("This data structure only accepts integer as an index.", 500);
        }

        throw new TimeSequenceViolationException("The new member doesn't respect the order of array members.
            New Start: " . $value->getStart()->format("Y-m-d H:i:s l") .
            " Last End: " . $this->dsDownTimes[array_key_last($this->dsDownTimes)]->getEnd()->format("Y-m-d H:i:s l"), 500);
    }

    public function offsetUnset(mixed $offset): void
    {
        if (!is_int($offset)) {
            throw new InvalidOffsetTypeException("Only Integer offset is accepted for unsetting a member.", 500);
        }

        if ($this->offsetExists($offset)) {
            unset($this->dsDownTimes[$offset]);
        }
    }

    // ------------------------------------ \Iterator

    public function current(): mixed
    {
        return $this->dsDownTimes[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        if (($lastKey = array_key_last($this->dsDownTimes)) === null) {
            $this->position++;
            return;
        }

        try {
            $this->position = $this->findNextPosition(function ($offset) {
                return isset($this->dsDownTimes[$offset]);
            }, $this->position, $lastKey);
        } catch (NoKeyFoundException $th) {
            $this->position++;
        }
    }

    public function prev(): void
    {
        if ($this->position === 0) {
            $this->position--;
            return;
        }

        try {
            $this->position = $this->findPreviousPosition(function ($offset) {
                return isset($this->dsDownTimes[$offset]);
            }, $this->position);
        } catch (NoKeyFoundException $th) {
            $this->position--;
        }
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->dsDownTimes[$this->position]);
    }

    // ------------------------------------ \Countable

    public function count(): int
    {
        return count($this->dsDownTimes);
    }
}
