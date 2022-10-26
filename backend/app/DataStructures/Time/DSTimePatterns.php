<?php

namespace App\DataStructures\Time;

use App\DataStructures\Interfaces\IClonable;
use App\DataStructures\Interfaces\Arrayable;
use App\DataStructures\Traits\TraitKeyPositioner;
use App\DataStructures\Exceptions\NoKeyFoundException;
use App\DataStructures\Exceptions\Time\InvalidOffsetTypeException;
use App\DataStructures\Exceptions\Time\TimeSequenceViolationException;

class DSTimePatterns implements
    \ArrayAccess,
    \Iterator,
    \Countable,
    \Stringable,
    IClonable,
    Arrayable
{
    use TraitKeyPositioner;

    /**
     * @var DSTimePattern[]
     */
    private array $dsTimePatterns;

    /**
     * position of the pointer of this data structure.(as we use it as a Iterable object)
     *
     * @var integer
     */
    private int $position;

    public function __construct()
    {
        $this->dsTimePatterns = [];
        $this->position = 0;
    }

    public function cloneIt(): self
    {
        $newDSDateTimePeriod = new self();

        foreach ($this->dsTimePatterns as $dsDownTime) {
            $newDSDateTimePeriod[] = $dsDownTime->cloneIt();
        }

        return $newDSDateTimePeriod;
    }

    public function toArray(): array
    {
        return array_map(function (DSTimePattern $dsTimePattern) {
            return $dsTimePattern->toArray();
        }, $this->dsTimePatterns);
    }

    public static function toObject(array $resultOfToArrayMethod): self
    {
        $dsTimePatterns = new static;

        foreach ($resultOfToArrayMethod as $timePattern) {
            $dsTimePattern = DSTimePattern::toObject($timePattern);
            $dsTimePatterns[] = $dsTimePattern;
        }

        return $dsTimePatterns;
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    private function validateValue(DSTimePattern $value): void
    {
        if (
            !empty($this->dsTimePatterns)
            &&
            (new \DateTime($this->dsTimePatterns[count($this->dsTimePatterns) - 1]->getEnd()))->getTimestamp() >= (new \DateTime($value->getStart()))->getTimestamp()
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
        return isset($this->dsTimePatterns[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->dsTimePatterns[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->validateOffset($offset);
        $this->validateValue($value);

        if (is_null($offset)) {
            $this->dsTimePatterns[] = $value;
            return;
        } elseif (is_int($offset)) {
            try {
                $previousKey = $this->findPreviousPosition([$this, "offsetExists"], $offset);
            } catch (NoKeyFoundException $th) {
            }

            try {
                if (($lastKey = array_key_last($this->dsTimePatterns)) !== null) {
                    $nextKey = $this->findNextPosition([$this, "offsetExists"], $offset, $lastKey);
                }
            } catch (NoKeyFoundException $th) {
            }

            if (isset($previousKey) && isset($nextKey)) {
                if (
                    (new \DateTime($this->dsTimePatterns[$previousKey]))->getTimestamp() < (new \DateTime($value))->getTimestamp()
                    &&
                    (new \DateTime($this->dsTimePatterns[$nextKey]))->getTimestamp() > (new \DateTime($value))->getTimestamp()
                ) {
                    $this->dsTimePatterns[$offset] = $value;
                    return;
                }
            } elseif (isset($previousKey)) {
                if ((new \DateTime($this->dsTimePatterns[$previousKey]))->getTimestamp() < (new \DateTime($value))->getTimestamp()) {
                    $this->dsTimePatterns[$offset] = $value;
                    return;
                }
            } elseif (isset($nextKey)) {
                if ((new \DateTime($this->dsTimePatterns[$nextKey]))->getTimestamp() > (new \DateTime($value))->getTimestamp()) {
                    $this->dsTimePatterns[$offset] = $value;
                    return;
                }
            } else {
                $this->dsTimePatterns[$offset] = $value;
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
            unset($this->dsTimePatterns[$offset]);
        }
    }

    // ------------------------------------ \Iterator

    public function current(): mixed
    {
        return $this->dsTimePatterns[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        if (($lastKey = array_key_last($this->dsTimePatterns)) === null) {
            $this->position++;
            return;
        }

        try {
            $this->position = $this->findNextPosition(function ($offset) {
                return isset($this->dsTimePatterns[$offset]);
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
                return isset($this->dsTimePatterns[$offset]);
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
        return isset($this->dsTimePatterns[$this->position]);
    }

    // ------------------------------------ \Countable

    public function count(): int
    {
        return count($this->dsTimePatterns);
    }
}
