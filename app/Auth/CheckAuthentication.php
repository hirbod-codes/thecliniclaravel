<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\DataStructures\User\ICheckAuthentication;

class CheckAuthentication implements ICheckAuthentication
{
    public function getAuthenticated(): Authenticatable|null
    {
        return Auth::user();
    }

    public function getAuthenticatedDSUser(): DSUser
    {
        /** @var \App\Models\User $user */
        $user = $this->getAuthenticated();

        return $user->getDataStructure();
    }

    public function isAuthenticated(DSUser $user): bool
    {
        return !is_null(Auth::user()) && isset(Auth::user()->id) && Auth::user()->id === $user->getId();
    }
}
