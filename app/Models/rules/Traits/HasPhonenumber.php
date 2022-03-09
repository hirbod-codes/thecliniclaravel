<?php

namespace App\Models\rules\Traits;

use App\Models\Phonenumber;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasPhonenumber
{
    public function phonenumber(): BelongsTo
    {
        return $this->belongsTo(
            Phonenumber::class,
            strtolower(class_basename(Phonenumber::class)) . '_' . (new Phonenumber)->getKeyName(),
            (new Phonenumber)->getKeyName()
        );
    }

    public function guardPhonenumberVerification(): void
    {
        $this->guarded[] = strtolower(class_basename(Phonenumber::class)) . '_verified_at';
    }

    public function castPhonenumberVerificationToDatetime(): void
    {
        $this->casts[strtolower(class_basename(Phonenumber::class)) . '_verified_at'] = 'datetime';
    }
}
