<?php

namespace App\Models\rules\Traits;

use App\Models\Phonenumber;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToPhonenumber
{
    public function phonenumber(): BelongsTo
    {
        return $this->belongsTo(
            Phonenumber::class,
            'phonenumber',
            'phonenumber',
            __FUNCTION__
        );
    }

    public function phonenumberVerifiedAt(): BelongsTo
    {
        return $this->belongsTo(
            Phonenumber::class,
            'phonenumber_verified_at',
            'phonenumber_verified_at',
            __FUNCTION__
        );
    }

    private function addPhonenumberForeignKey(): void
    {
        $this->foreignKeys['phonenumber'] = 'phonenumber';
    }

    private function addPhonenumberVerifiedAtForeignKey(): void
    {
        $this->foreignKeys['phonenumber'] = 'phonenumber_verified_at';
    }

    public function guardPhonenumberVerification(): void
    {
        $this->guarded[] = 'phonenumber_verified_at';
    }

    public function castPhonenumberVerificationToDatetime(): void
    {
        $this->casts['phonenumber_verified_at'] = 'datetime';
    }
}
