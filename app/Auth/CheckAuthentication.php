<?php

namespace App\Auth;

use App\Models\Auth\User as Authenticatable;
use App\Models\Model;
use Illuminate\Support\Facades\Auth;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\DataStructures\User\ICheckAuthentication;
use TheClinicUseCases\Accounts\ICheckForAuthenticatedUsers;

class CheckAuthentication implements
    ICheckAuthentication,
    ICheckForAuthenticatedUsers
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

    public function getAuthenticatedDSUser(): DSUser
    {
        /** @var \App\Models\User $user */
        if (($user = $this->getAuthenticated()) === null) {
            throw new \RuntimeException('There is no authenticated user.', 401);
        }

        return $user->getDataStructure();
    }

    public function isAuthenticated(DSUser $user): bool
    {
        if (($authenticated = $this->getAuthenticated()) === null) {
            return false;
        }

        if ($authenticated instanceof Model) {
            $primaryKey = (new $authenticated)->getKeyName();
        } else {
            $primaryKey = 'id';
        }

        return !is_null($authenticated) && isset($authenticated->{$primaryKey}) && $authenticated->{$primaryKey} === $user->getId();
    }
}
