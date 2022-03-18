<?php

namespace App\Models\rules\Traits;

use App\Models\Email;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToEmail
{
    public function email(): BelongsTo
    {
        return $this->belongsTo(
            Email::class,
            'email',
            'email',
            __FUNCTION__
        );
    }

    public function emailVerifiedAt(): BelongsTo
    {
        return $this->belongsTo(
            Email::class,
            'email_verified_at',
            'email_verified_at',
            __FUNCTION__
        );
    }

    private function addEmailForeignKey(): void
    {
        $this->foreignKeys['email'] = 'email';
    }

    private function addEmailVerifiedAtForeignKey(): void
    {
        $this->foreignKeys['email'] = 'email_verified_at';
    }

    public function guardEmailVerification(): void
    {
        $this->guarded[] = 'email_verified_at';
    }

    public function castEmailVerificationToDatetime(): void
    {
        $this->casts['email_verified_at'] = 'datetime';
    }
}
