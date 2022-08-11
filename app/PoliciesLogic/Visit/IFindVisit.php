<?php

namespace App\PoliciesLogic\Visit;

interface IFindVisit
{
    /**
     * Finds a new available visit and returns it's timestamp.
     *
     * @return integer
     */
    public function findVisit(): int;
}
