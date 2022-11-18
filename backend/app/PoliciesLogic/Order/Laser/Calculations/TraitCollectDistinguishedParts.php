<?php

namespace App\PoliciesLogic\Order\Laser\Calculations;

use App\DataStructures\Order\DSPackage;
use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSPart;
use App\DataStructures\Order\DSParts;

trait TraitCollectDistinguishedParts
{
    /**
     * It collects parts from $packages that don't already exist in $parts and then adds them to $parts
     * so we can have all the unique parts in $parts and $packages.
     *
     * @param \App\DataStructures\Order\DSParts $parts
     * @param \App\DataStructures\Order\DSPackages $packages
     * @return array
     */
    private function collectDistinguishedParts(DSParts $dsParts, DSPackages $packages): array
    {
        $packagesParts = [];
        $ids = $this->collectIdsFromDSParts($dsParts);

        /** @var DSPart $dsPackagePart */
        foreach ($this->collectDSPartFromDSPackages($packages) as $dsPackagePart) {
            if (empty($ids) || !in_array($dsPackagePart->getId(), $ids)) {
                $packagesParts[] = $dsPackagePart;
            }
        }

        foreach ($dsParts as $dsPart) {
            $packagesParts[] = $dsPart;
        }

        return $packagesParts;
    }

    private function collectDSPartFromDSPackages(DSPackages $dsPackages): \Generator
    {
        /** @var DSPackage $dsPackage */
        foreach ($dsPackages as $dsPackage) {
            /** @var DSPart $dsPart */
            foreach ($dsPackage->getParts() as $dsPart) {
                yield $dsPart;
            }
        }
    }

    /**
     * @param DSParts $dsParts
     * @return int[]
     */
    private function collectIdsFromDSParts(DSParts $dsParts): array
    {
        $ids = [];
        /** @var DSPart $dsPart */
        foreach ($dsParts as $dsPart) {
            $ids[] = $dsPart->getId();
        }

        return $ids;
    }

    private function collectPartsThatDontExistInPackages(DSParts $dsParts, DSPackages $dsPackages): array
    {
        $parts = [];

        if (count($dsPackages) === 0) {
            foreach ($dsParts as $dsPart) {
                $parts[] = $dsPart;
            }

            return $parts;
        }

        /** @var DSPart $dsPart */
        foreach ($dsParts as $dsPart) {
            /** @var DSParts $dsParts */
            foreach ($this->collectDSPartsFromDSPackages($dsPackages) as $dsParts) {
                if (!in_array($dsPart->getId(), $this->collectIdsFromDSParts($dsParts))) {
                    $parts[] = $dsPart;
                }
            }
        }

        return $parts;
    }

    private function collectDSPartsFromDSPackages(DSPackages $dsPackages): \Generator
    {
        /** @var DSPackage $dsPackage */
        foreach ($dsPackages as $dsPackage) {
            yield $dsPackage->getParts();
        }
    }
}
