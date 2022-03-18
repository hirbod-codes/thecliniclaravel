<?php

namespace App\Models\rules\Traits;

use App\Models\Email;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait MorphOneEmail
{
    public function morphEmail(): MorphOne
    {
        return $this->morphOne(
            Email::class,
            'authenticatable',
            'authenticatable_type',
            'authenticatable_id',
            'email'
        );
    }

    public function morphEmailVerifiedAt(): MorphOne
    {
        return $this->morphOne(
            Email::class,
            'authenticatable',
            'authenticatable_type',
            'authenticatable_id',
            'email_verified_at'
        );
    }
}
