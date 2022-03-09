<?php

namespace App\Models\rules\Traits;

use App\Models\Email;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasEmail
{
    public function email(): BelongsTo
    {
        return $this->belongsTo(
            Email::class,
            strtolower(class_basename(Email::class)) . '_' . (new Email)->getKeyName(),
            (new Email)->getKey()
        );
    }

    public function guardEmailVerification(): void
    {
        $this->guarded[] = strtolower(class_basename(Email::class)) . '_verified_at';
    }

    public function castEmailVerificationToDatetime(): void
    {
        $this->casts[strtolower(class_basename(Email::class)) . '_verified_at'] = 'datetime';
    }
}
