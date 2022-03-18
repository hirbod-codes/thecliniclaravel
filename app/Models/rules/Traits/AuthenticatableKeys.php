<?php

namespace App\Models\rules\Traits;

trait AuthenticatableKeys
{
    public function getAuthenticatableForeignKey(): string
    {
        if (static::class === Rule::class) {
            return $this->getForeignKey();
        } else {
            return lcfirst(class_basename(static::class));
        }
    }

    public function getAuthenticatableLocalKey(): string
    {
        if (static::class === Rule::class) {
            return $this->getKeyName();
        } else {
            return lcfirst(class_basename(static::class));
        }
    }
}
