<?php

namespace App\Models;

use App\Models\Auth\User as Authenticatable;
use App\Models\Order\Order;
use App\Models\Auth\Admin;
use App\Models\Auth\Doctor;
use App\Models\Auth\Operator;
use App\Models\Auth\Patient;
use App\Models\Auth\Secretary;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;

/**
 * @property Admin|Doctor|Operator|Patient|Secretary $authenticatableRole hasOne
 * @property Admin $childModelAdmin hasOne
 * @property Doctor $childModelDoctor hasOne
 * @property Operator $childModelOperator hasOne
 * @property Patient $childModelPatient hasOne
 * @property Secretary $childModelSecretary hasOne
 * @property Collection<int, Order> $orders hasMany
 * @property string $firstname
 * @property string $lastname
 * @property string $username
 * @property string $password
 * @property string $email
 * @property \DateTime $email_verified_at
 * @property string $phonenumber
 * @property \DateTime $phonenumber_verified_at
 * @property string $gender
 */
class User extends Authenticatable
{
    use HasFactory,
        HasApiTokens,
        Notifiable;

    protected $table = "users";

    public function getChildrenTypesRelationNames(): array
    {
        $relations = [];
        /** @var \ReflectionMethod $method */
        foreach (($class = new \ReflectionClass($this))->getMethods() as $method) {
            if (Str::startsWith($method->getName(), 'childModel') && $method->getReturnType() !== null && $method->getReturnType()->getName() === HasOne::class) {
                $relations[] = $method->getName();
            }
        }
        return $relations;
    }

    public function authenticatableRole(): HasOne
    {
        /** @var \ReflectionMethod $method */
        foreach (($class = new \ReflectionClass($this))->getMethods() as $method) {
            $result = $results = null;
            $methodName = $method->getName();
            if (Str::startsWith($methodName, 'childModel') && $method->getReturnType() !== null && $method->getReturnType()->getName() === HasOne::class) {
                $result = $this->$methodName();
                $results = $result->getResults();
                if (!($results === null || ($results instanceof \Countable && count($results) === 0))) {
                    return $result;
                }
            }
        }
    }

    public function childModelAdmin(): HasOne
    {
        return $this->hasOne(
            Admin::class,
            $this->getForeignKey(),
            $this->getKeyName(),
            __FUNCTION__
        );
    }

    public function childModelDoctor(): HasOne
    {
        return $this->hasOne(
            Doctor::class,
            $this->getForeignKey(),
            $this->getKeyName(),
            __FUNCTION__
        );
    }

    public function childModelSecretary(): HasOne
    {
        return $this->hasOne(
            Secretary::class,
            $this->getForeignKey(),
            $this->getKeyName(),
            __FUNCTION__
        );
    }

    public function childModelOperator(): HasOne
    {
        return $this->hasOne(
            Operator::class,
            $this->getForeignKey(),
            $this->getKeyName(),
            __FUNCTION__
        );
    }

    public function childModelPatient(): HasOne
    {
        return $this->hasOne(
            Patient::class,
            $this->getForeignKey(),
            $this->getKeyName(),
            __FUNCTION__
        );
    }

    public function orders(): HasMany
    {
        return $this->hasMany(
            Order::class,
            $this->getForeignKey(),
            $this->getKeyName(),
            __FUNCTION__
        );
    }

    /**
     * Find the user instance for the given username.
     *
     * @param  string  $username
     * @return \App\Models\User
     */
    public function findForPassport($credential)
    {
        if (Str::contains($credential, '@')) {
            return $this->where('email', $credential)->first();
        } else {
            return $this->where('username', $credential)->first();
        }
    }
}
