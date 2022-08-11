<?php

namespace App\DataStructures\Traits;

use App\DataStructures\Exceptions\NoKeyFoundException;

trait TraitKeyPositioner
{
    /**
     * Finds next position if it exists, otherwise it will throw an exception.
     *
     * @param array|\Closure $checkOffsetExistance A closure that accepts an offset and returns true if it exists in the target.
     * @return integer
     */
    private function findPreviousPosition(array|\Closure $checkOffsetExistance, int $offset): int
    {
        $pastKey = $offset - 1;
        while (!$checkOffsetExistance($pastKey) && $pastKey > 0) {
            $pastKey--;
        }

        if (!$checkOffsetExistance($pastKey)) {
            throw new NoKeyFoundException("There is no previous key in the target.", 500);
        }

        return $pastKey;
    }

    /**
     * Finds next position if it exists, otherwise it will throw an exception.
     *
     * @param array|\Closure $checkOffsetExistance A closure that accepts an offset and returns true if it exists in the target.
     * @return integer
     */
    private function findNextPosition(array|\Closure $checkOffsetExistance, int $offset, int $targetLastKey): int
    {
        $nextKey = $offset + 1;
        while ($nextKey < $targetLastKey && !$checkOffsetExistance($nextKey)) {
            $nextKey++;
        }

        if (!$checkOffsetExistance($nextKey)) {
            throw new NoKeyFoundException("There is no next key in the target.", 500);
        }

        return $nextKey;
    }
}
