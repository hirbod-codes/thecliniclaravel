<?php

namespace App\DataStructures\Time;

use App\DataStructures\Interfaces\IClonable;
use App\DataStructures\Interfaces\Arrayable;
use App\DataStructures\Exceptions\Time\TimeSequenceViolationException;

class DSTimePattern implements IClonable, Arrayable, \Stringable
{
    protected string $start;

    protected string $end;

    public function __construct(string $start, string $end)
    {
        $this->setStart($start);
        $this->setEnd($end);
    }

    public function toArray(): array
    {
        return [
            'start' => $this->getStart(),
            'end' => $this->getEnd(),
        ];
    }

    public static function toObject(array $resultOfToArrayMethod): self
    {
        return new static(
            $resultOfToArrayMethod['start'],
            $resultOfToArrayMethod['end']
        );
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * @param string $start
     * @return void
     *
     * @throws TimeSequenceViolationException
     */
    public function validateStartInsert(string $start): void
    {
        if (preg_match('/\A([0-1]{1}[0-9]{1}|2[0-4]{1}):[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}\z/', $start) !== 1) {
            throw new \InvalidArgumentException("unacceptable format for input variable \$start: " . $start);
        }

        if (isset($this->end) && (new \DateTime($start))->getTimestamp() >= (new \DateTime($this->end))->getTimestamp()) {
            throw new TimeSequenceViolationException("The 'start' property must be before the 'end' property.(in terms of time)", 500);
        }
    }

    /**
     * @param string $end
     * @return void
     *
     * @throws TimeSequenceViolationException
     */
    public function validateEndInsert(string $end): void
    {
        if (preg_match('/\A([0-1]{1}[0-9]{1}|2[0-4]{1}):[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}\z/', $end) !== 1) {
            throw new \InvalidArgumentException("unacceptable format for input variable \$end: " . $end);
        }

        if (isset($this->start) && (new \DateTime($end))->getTimestamp() <= (new \DateTime($this->start))->getTimestamp()) {
            throw new TimeSequenceViolationException("The 'start' property must be before the 'end' property.(in terms of time)", 500);
        }
    }

    public function setStart(string $start): void
    {
        $this->validateStartInsert($start);

        $this->start = $start;
    }

    public function getStart(): string
    {
        return $this->start;
    }

    public function setEnd(string $end): void
    {
        $this->validateEndInsert($end);

        $this->end = $end;
    }

    public function getEnd(): string
    {
        return $this->end;
    }

    public function cloneIt(): self
    {
        return new self($this->start, $this->end);
    }
}
