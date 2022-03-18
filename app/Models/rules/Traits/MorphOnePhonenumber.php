<?php

namespace App\Models\rules\Traits;

use App\Models\Phonenumber;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait MorphOnePhonenumber
{
    public function morphPhonenumber(): MorphOne
    {
        return $this->morphOne(
            Phonenumber::class,
            'authenticatable',
            'authenticatable_type',
            'authenticatable_id',
            'phonenumber'
        );
    }

    public function morphPhonenumberVerifiedAt(): MorphOne
    {
        return $this->morphOne(
            Phonenumber::class,
            'authenticatable',
            'authenticatable_type',
            'authenticatable_id',
            'phonenumber_verified_at'
        );
    }
}
