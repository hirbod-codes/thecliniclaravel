<?php

namespace App\DataStructures\Visit;

use App\DataStructures\Time\DSDateTimePeriods;
use App\DataStructures\Time\DSWeeklyTimePatterns;

abstract class DSVisit
{
    private int $id;

    private int $visitTimestamp;

    private int $consumingTime;

    public null|DSWeeklyTimePatterns $weeklyTimePatterns;

    public null|DSDateTimePeriods $dateTimePeriods;

    private \DateTime $createdAt;

    private \DateTime $updatedAt;

    /**
     * @param integer $id
     * @param integer $visitTimestamp
     * @param integer $consumingTime
     * @param \DateTime $createdAt
     * @param \DateTime $updatedAt
     * @param DSWeeklyTimePatterns|null $weeklyTimePatterns must not have a value other than null at present of $dateTimePeriods.
     * @param DSDateTimePeriods|null $dateTimePeriods must not have a value other than null at present of $weeklyTimePatterns.
     */
    public function __construct(
        int $id,
        int $visitTimestamp,
        int $consumingTime,
        \DateTime $createdAt,
        \DateTime $updatedAt,
        DSWeeklyTimePatterns|null $weeklyTimePatterns = null,
        DSDateTimePeriods|null $dateTimePeriods = null,
    ) {
        if (!is_null($weeklyTimePatterns) && !is_null($dateTimePeriods)) {
            throw new \LogicException("\$weeklyTimePatterns and \$dateTimePeriods can't have a value beside null at the same time.", 500);
        }

        $this->id = $id;
        $this->visitTimestamp = $visitTimestamp;
        $this->consumingTime = $consumingTime;
        $this->weeklyTimePatterns = $weeklyTimePatterns;
        $this->dateTimePeriods = $dateTimePeriods;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'visitTimestamp' => $this->visitTimestamp,
            'consumingTime' => $this->consumingTime,
            'weeklyTimePatterns' => $this->weeklyTimePatterns === null ? null : $this->weeklyTimePatterns->toArray(),
            'dateTimePeriods' => $this->dateTimePeriods === null ? null : $this->dateTimePeriods->toArray(),
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
