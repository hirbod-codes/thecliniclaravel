<?php

namespace App\Auth;

use App\Models\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;

class CheckAuthentication
{
    public function checkIfThereIsNoAuthenticated(): bool
    {
        return $this->getAuthenticated() === null;
    }

    public function getAuthenticated(): Authenticatable|null
    {
        foreach ($guards = app()['config']["auth.guards"] as $name => $guard) {
            if (($user = Auth::guard($name)->user()) !== null) {
                return $user;
            }
        }

        return null;
    }
}
