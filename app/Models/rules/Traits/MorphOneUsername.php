<?php

namespace App\Models\rules\Traits;

use App\Models\Username;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait MorphOneUsername
{
    public function morphUsername(): MorphOne
    {
        return $this->morphOne(
            Username::class,
            'authenticatable',
            'authenticatable_type',
            'authenticatable_id',
            'username'
        );
    }
}
