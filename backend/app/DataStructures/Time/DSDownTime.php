<?php

namespace App\DataStructures\Time;

use App\DataStructures\Interfaces\IClonable;
use App\DataStructures\Time\DSDateTimePeriod;
use App\DataStructures\Interfaces\Arrayable;

class DSDownTime extends DSDateTimePeriod implements IClonable, Arrayable, \Stringable
{
    private string $name;

    public function __construct(\DateTime $start, \DateTime $end, string $name)
    {
        $this->setStart($start);
        $this->setEnd($end);
        $this->name = $name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function cloneIt(): DSDownTime
    {
        return new DSDownTime(
            (new \DateTime())->setTimestamp($this->start->getTimestamp()),
            (new \DateTime())->setTimestamp($this->end->getTimestamp()),
            $this->name
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'start' => $this->getStart()->format("Y-m-d H:i:s"),
            'end' => $this->getEnd()->format("Y-m-d H:i:s"),
        ];
    }

    public static function toObject(array $resultOfToArrayMethod): self
    {
        return new static(
            new \DateTime($resultOfToArrayMethod['start']),
            new \DateTime($resultOfToArrayMethod['end']),
            $resultOfToArrayMethod['name']
        );
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }
}
