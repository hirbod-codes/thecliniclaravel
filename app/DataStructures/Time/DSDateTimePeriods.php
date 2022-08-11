<?php

namespace App\DataStructures\Time;

use App\DataStructures\Interfaces\IClonable;
use App\DataStructures\Time\DSDateTimePeriod;
use App\DataStructures\Interfaces\Arrayable;
use App\DataStructures\Traits\TraitKeyPositioner;
use App\DataStructures\Exceptions\NoKeyFoundException;
use App\DataStructures\Exceptions\Time\InvalidOffsetTypeException;
use App\DataStructures\Exceptions\Time\TimeSequenceViolationException;

class DSDateTimePeriods implements
    \ArrayAccess,
    \Iterator,
    \Countable,
    \Stringable,
    IClonable,
    Arrayable
{
    use TraitKeyPositioner;

    /**
     * @var DSDateTimePeriod[]
     */
    private array $dsDateTimePeriods;

    /**
     * position of the pointer of this data structure.(as we use it as a Iterable object)
     *
     * @var integer
     */
    private int $position;

    public function __construct()
    {
        $this->dsDateTimePeriods = [];
        $this->position = 0;
    }

    public function cloneIt(): self
    {
        $newDSDateTimePeriod = new DSDateTimePeriods();

        foreach ($this->dsDateTimePeriods as $dsDownTime) {
            $newDSDateTimePeriod[] = $dsDownTime->cloneIt();
        }

        return $newDSDateTimePeriod;
    }

    public function toArray(): array
    {
        return array_map(function (DSDateTimePeriod $dsDateTimePeriod) {
            return $dsDateTimePeriod->toArray();
        }, $this->dsDateTimePeriods);
    }

    public static function toObject(array $resultOfToArrayMethod): self
    {
        $dsDateTimePeriods = new static;

        foreach ($resultOfToArrayMethod as $dateTimePeriod) {
            $dsDateTimePeriod = DSDateTimePeriod::toObject($dateTimePeriod);
            $dsDateTimePeriods[] = $dsDateTimePeriod;
        }

        return $dsDateTimePeriods;
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    private function validateValue(DSDateTimePeriod $value): void
    {
        if (
            !empty($this->dsDateTimePeriods)
            &&
            $this->dsDateTimePeriods[count($this->dsDateTimePeriods) - 1]->getEndTimestamp() >= $value->getStartTimestamp()
        ) {
            throw new TimeSequenceViolationException('The new inserting value doesn\'t respect the order of date times.');
        }
    }

    private function validateOffset(int|null $offset): void
    {
    }

    // ------------------------------------ \ArrayAccess

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->dsDateTimePeriods[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->dsDateTimePeriods[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->validateOffset($offset);
        $this->validateValue($value);

        if (is_null($offset)) {
            $this->dsDateTimePeriods[] = $value;
            return;
        } elseif (is_int($offset)) {
            try {
                $previousKey = $this->findPreviousPosition([$this, "offsetExists"], $offset);
            } catch (NoKeyFoundException $th) {
            }

            try {
                if (($lastKey = array_key_last($this->dsDateTimePeriods)) !== null) {
                    $nextKey = $this->findNextPosition([$this, "offsetExists"], $offset, $lastKey);
                }
            } catch (NoKeyFoundException $th) {
            }

            if (isset($previousKey) && isset($nextKey)) {
                if (
                    $this->dsDateTimePeriods[$previousKey]->getEndTimestamp() < $value->getStartTimestamp()
                    &&
                    $this->dsDateTimePeriods[$nextKey]->getStartTimestamp() > $value->getEndTimestamp()
                ) {
                    $this->dsDateTimePeriods[$offset] = $value;
                    return;
                }
            } elseif (isset($previousKey)) {
                if ($this->dsDateTimePeriods[$previousKey]->getEndTimestamp() < $value->getStartTimestamp()) {
                    $this->dsDateTimePeriods[$offset] = $value;
                    return;
                }
            } elseif (isset($nextKey)) {
                if ($this->dsDateTimePeriods[$nextKey]->getStartTimestamp() > $value->getEndTimestamp()) {
                    $this->dsDateTimePeriods[$offset] = $value;
                    return;
                }
            } else {
                $this->dsDateTimePeriods[$offset] = $value;
                return;
            }
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        if (!is_int($offset)) {
            throw new InvalidOffsetTypeException("Only Integer offset is accepted for unsetting a member.", 500);
        }

        if ($this->offsetExists($offset)) {
            unset($this->dsDateTimePeriods[$offset]);
        }
    }

    // ------------------------------------ \Iterator

    public function current(): mixed
    {
        return $this->dsDateTimePeriods[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        if (($lastKey = array_key_last($this->dsDateTimePeriods)) === null) {
            $this->position++;
            return;
        }

        try {
            $this->position = $this->findNextPosition(function ($offset) {
                return isset($this->dsDateTimePeriods[$offset]);
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
                return isset($this->dsDateTimePeriods[$offset]);
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
        return isset($this->dsDateTimePeriods[$this->position]);
    }

    // ------------------------------------ \Countable

    public function count(): int
    {
        return count($this->dsDateTimePeriods);
    }
}
