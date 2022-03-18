<?php

namespace App\Models\rules\Traits;

trait AuthenticatableVerifiedAtKeys
{
    public function getAuthenticatableVerifiedAtForeignKey(): string
    {
        return lcfirst(class_basename(static::class)) . '_verified_at';
    }

    public function getAuthenticatableVerifiedAtLocalKey(): string
    {
        return lcfirst(class_basename(static::class)) . '_verified_at';
    }
}
