<?php

namespace App\DataStructures\Time;

use App\DataStructures\Interfaces\IClonable;
use App\DataStructures\Interfaces\Arrayable;
use App\DataStructures\Exceptions\Time\TimeSequenceViolationException;

class DSDateTimePeriod implements IClonable, Arrayable, \Stringable
{
    protected \DateTime $start;

    protected \DateTime $end;

    public function __construct(\DateTime $start, \DateTime $end)
    {
        $this->setStart($start);
        $this->setEnd($end);
    }

    public function toArray(): array
    {
        return [
            'start' => $this->getStart()->format("Y-m-d H:i:s"),
            'end' => $this->getEnd()->format("Y-m-d H:i:s"),
        ];
    }

    public static function toObject(array $resultOfToArrayMethod): self
    {
        return new static(
            new \DateTime($resultOfToArrayMethod['start']),
            new \DateTime($resultOfToArrayMethod['end'])
        );
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * @param \DateTime $start
     * @return void
     *
     * @throws TimeSequenceViolationException
     */
    public function validateStartInsert(\DateTime $start): void
    {
        if (isset($this->end) && $start->getTimestamp() >= $this->end->getTimestamp()) {
            throw new TimeSequenceViolationException("The 'start' property must be before the 'end' property.(in terms of time)", 500);
        }
    }

    /**
     * @param \DateTime $end
     * @return void
     *
     * @throws TimeSequenceViolationException
     */
    public function validateEndInsert(\DateTime $end): void
    {
        if (isset($this->start) && $end->getTimestamp() <= $this->start->getTimestamp()) {
            throw new TimeSequenceViolationException("The 'start' property must be before the 'end' property.(in terms of time)", 500);
        }
    }

    public function setStart(\DateTime $start): void
    {
        $this->validateStartInsert($start);

        $this->start = $start;
    }

    public function getStart(): \DateTime
    {
        return $this->start;
    }

    public function setEnd(\DateTime $end): void
    {
        $this->validateEndInsert($end);

        $this->end = $end;
    }

    public function getEnd(): \DateTime
    {
        return $this->end;
    }

    public function getStartTimestamp(): int
    {
        return $this->start->getTimestamp();
    }

    public function getEndTimestamp(): int
    {
        return $this->end->getTimestamp();
    }

    public function cloneIt(): DSDateTimePeriod
    {
        return new DSDateTimePeriod((new \DateTime())->setTimestamp($this->start->getTimestamp()), (new \DateTime())->setTimestamp($this->end->getTimestamp()));
    }

    public function setDate(\DateTime $date): void
    {
        $this->start = new \DateTime($date->format('Y-m-d') . ' ' . $this->start->format('H:i:s'));
        $this->end = new \DateTime($date->format('Y-m-d') . ' ' . $this->end->format('H:i:s'));
        $this->validateStartInsert($this->start);
        $this->validateEndInsert($this->end);
    }

    public function setTime(\DateTime $time): void
    {
        $this->start = new \DateTime($this->start->format('Y-m-d') . ' ' . $time->format('H:i:s'));
        $this->end = new \DateTime($this->end->format('Y-m-d') . ' ' . $time->format('H:i:s'));
        $this->validateStartInsert($this->start);
        $this->validateEndInsert($this->end);
    }

    public function setStartDate(\DateTime $date): void
    {
        $this->setStart(new \DateTime($date->format('Y-m-d') . ' ' . $this->start->format('H:i:s')));
    }

    public function setStartTime(\DateTime $time): void
    {
        $this->setStart(new \DateTime($this->start->format('Y-m-d') . ' ' . $time->format('H:i:s')));
    }

    public function setEndDate(\DateTime $date): void
    {
        $this->setEnd(new \DateTime($date->format('Y-m-d') . ' ' . $this->end->format('H:i:s')));
    }

    public function setEndTime(\DateTime $time): void
    {
        $this->setEnd(new \DateTime($this->end->format('Y-m-d') . ' ' . $time->format('H:i:s')));
    }
}
