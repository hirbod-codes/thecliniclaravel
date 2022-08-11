<?php

namespace App\DataStructures\Visit;

use App\DataStructures\Time\DSDateTimePeriod;
use App\DataStructures\Time\DSWeekDaysPeriods;

abstract class DSVisit
{
    private int $id;

    private int $visitTimestamp;

    private int $consumingTime;

    public null|DSWeekDaysPeriods $weekDaysPeriods;

    public null|DSDateTimePeriod $dateTimePeriod;

    private \DateTime $createdAt;

    private \DateTime $updatedAt;

    /**
     * @param integer $id
     * @param integer $visitTimestamp
     * @param integer $consumingTime
     * @param \DateTime $createdAt
     * @param \DateTime $updatedAt
     * @param DSWeekDaysPeriods|null|null $weekDaysPeriods must not have a value other than null at present of $dateTimePeriod.
     * @param DSDateTimePeriod|null|null $dateTimePeriod must not have a value other than null at present of $weekDaysPeriods.
     */
    public function __construct(
        int $id,
        int $visitTimestamp,
        int $consumingTime,
        \DateTime $createdAt,
        \DateTime $updatedAt,
        DSWeekDaysPeriods|null $weekDaysPeriods = null,
        DSDateTimePeriod|null $dateTimePeriod = null,
    ) {
        if (!is_null($weekDaysPeriods) && !is_null($dateTimePeriod)) {
            throw new \LogicException("\$weekDaysPeriods and \$dateTimePeriod can't have a value beside null at the same time.", 500);
        }

        $this->id = $id;
        $this->visitTimestamp = $visitTimestamp;
        $this->consumingTime = $consumingTime;
        $this->weekDaysPeriods = $weekDaysPeriods;
        $this->dateTimePeriod = $dateTimePeriod;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'visitTimestamp' => $this->visitTimestamp,
            'consumingTime' => $this->consumingTime,
            'weekDaysPeriods' => $this->weekDaysPeriods === null ? null : $this->weekDaysPeriods->toArray(),
            'dateTimePeriod' => $this->dateTimePeriod === null ? null : $this->dateTimePeriod->toArray(),
            'createdAt' => $this->createdAt->format("Y-m-d H:i:s"),
            'updatedAt' => $this->updatedAt->format("Y-m-d H:i:s")
        ];
    }

    public function setId(int $val): void
    {
        $this->id = $val;
    }

    public function setVisitTimestamp(int $val): void
    {
        $this->visitTimestamp = $val;
    }

    public function setConsumingTime(int $val): void
    {
        $this->consumingTime = $val;
    }

    public function setCreatedAt(\DateTime $val): void
    {
        $this->createdAt = $val;
    }

    public function setUpdatedAt(\DateTime $val): void
    {
        $this->updatedAt = $val;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getVisitTimestamp(): int
    {
        return $this->visitTimestamp;
    }

    public function getConsumingTime(): int
    {
        return $this->consumingTime;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }
}
