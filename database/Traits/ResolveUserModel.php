<?php

namespace Database\Traits;

use App\Models\User;

trait ResolveUserModel
{
    public function resolveRuleModelFullName(string $ruleName): string
    {
        if ($ruleName === 'custom') {
            return User::class;
        }

        return 'App\\Models\\rules\\' . ucfirst($ruleName) . 'Rule';
    }
}

