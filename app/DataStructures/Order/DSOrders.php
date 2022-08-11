<?php

namespace App\DataStructures\Order;

use App\DataStructures\Interfaces\Arrayable;
use App\DataStructures\Traits\TraitKeyPositioner;
use App\DataStructures\Exceptions\NoKeyFoundException;
use App\DataStructures\Exceptions\Order\InvalidOffsetTypeException;
use App\DataStructures\Exceptions\Order\InvalidUserException;
use App\DataStructures\Exceptions\Order\InvalidValueTypeException;
use App\Models\User;

class DSOrders implements \ArrayAccess, \Iterator, \Countable, \Stringable, Arrayable
{
    use TraitKeyPositioner;

    public User|null $user;

    protected array $orders = [];

    protected int $position;

    public function __construct(User|null $user = null)
    {
        $this->user = $user;
        $this->position = 0;
    }

    public function toArray(): array
    {
        return [
            'user' => $this->user === null ? null : $this->user->toArray(),
            'orders' => array_map(function (DSOrder $order) {
                return $order->toArray();
            }, $this->orders)
        ];
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    public function isMixedOrders(): bool
    {
        return $this->mixedOrders;
    }

    // -------------------- \ArrayAccess

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->orders[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (gettype($offset) !== "integer") {
            throw new InvalidOffsetTypeException("This data structure only accepts integer as an offset type.", 500);
        }

        return $this->orders[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (gettype($offset) !== "integer" && !is_null($offset)) {
            throw new InvalidOffsetTypeException("This data structure only accepts integer and null as an offset type.", 500);
        }

        $this->checkOrderType($value);

        if (isset($this->user) && !is_null($this->user) && $this->user->getKey() !== $value->getUserId()) {
            throw new InvalidUserException("The members of this data structure must belong to the same specified user. Mismatched member id: " . $value->getId(), 500);
        }

        if (is_null($offset)) {
            $this->orders[] = $value;
        } elseif (gettype($offset) === "integer") {
            $this->orders[$offset] = $value;
        }
    }

    /**
     * @param \App\DataStructures\Order\DSOrder $order
     * @return void
     *
     * @throws \App\DataStructures\Exceptions\Order\InvalidValueTypeException
     */
    protected function checkOrderType(DSOrder $order): void
    {
        if (!($order instanceof DSOrder)) {
            throw new InvalidValueTypeException("This data structure only accepts the type: " . DSOrder::class . " as an array member.", 500);
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->orders[$offset]);
    }

    // -------------------- \Iterator

    public function current(): mixed
    {
        return $this->orders[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        if (($lastKey = array_key_last($this->orders)) === null) {
            $this->position++;
            return;
        }

        try {
            $this->position = $this->findNextPosition(function ($offset) {
                return isset($this->orders[$offset]);
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
        return isset($this->orders[$this->position]);
    }

    // ------------------------------------ \Countable

    public function count(): int
    {
        return count($this->orders);
    }
}
