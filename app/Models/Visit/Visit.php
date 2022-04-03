<?php

namespace App\Models\Visit;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use TheClinicDataStructures\DataStructures\Visit\DSVisit;
use TheClinicDataStructures\DataStructures\Visit\DSVisits;

class Visit extends Model
{
    use HasFactory;

    protected $table = 'visits';

    public function laserVisit(): HasOne
    {
        return $this->hasOne(
            LaserVisit::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }

    public function regularVisit(): HasOne
    {
        return $this->hasOne(
            RegularVisit::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }
}
