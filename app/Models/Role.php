<?php

namespace App\Models;

use App\Models\Privileges\CreateOrder;
use App\Models\Privileges\CreateUser;
use App\Models\Privileges\CreateVisit;
use App\Models\Privileges\DeleteOrder;
use App\Models\Privileges\DeleteUser;
use App\Models\Privileges\DeleteVisit;
use App\Models\Privileges\RetrieveOrder;
use App\Models\Privileges\RetrieveUser;
use App\Models\Privileges\RetrieveVisit;
use App\Models\Privileges\UpdateUser;
use App\Models\Roles\AdminRole;
use App\Models\Roles\DoctorRole;
use App\Models\Roles\OperatorRole;
use App\Models\Roles\PatientRole;
use App\Models\Roles\SecretaryRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory;

    protected $table = "roles";

    protected $guarded = ['*'];

    public function childRoleModel(): HasOne
    {
        /** @var \ReflectionMethod $method */
        foreach (($class = new \ReflectionClass($this))->getMethods() as $method) {
            if (Str::endsWith($method->getName(), 'Role') && $method->getReturnType()->getName() === HasOne::class && ($result = $this->{ $method->getName()}())->getResults() !== null) {
                return $result;
            }
        }
    }

    public function adminRole(): HasOne
    {
        return $this->hasOne(
            AdminRole::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }

    public function doctorRole(): HasOne
    {
        return $this->hasOne(
            DoctorRole::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }

    public function secretaryRole(): HasOne
    {
        return $this->hasOne(
            SecretaryRole::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }

    public function operatorRole(): HasOne
    {
        return $this->hasOne(
            OperatorRole::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }

    public function patientRole(): HasOne
    {
        return $this->hasOne(
            PatientRole::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }

    // ---------------------------------------------------------------------------------------------------------------------------------------

    public function createUserSubjects(): HasMany
    {
        return $this->hasMany(
            CreateUser::class,
            'subject',
            $this->getKeyName()
        );
    }

    public function createUserObjects(): HasMany
    {
        return $this->hasMany(
            CreateUser::class,
            'object',
            $this->getKeyName()
        );
    }

    public function retrieveUserSubjects(): HasMany
    {
        return $this->hasMany(
            RetrieveUser::class,
            'subject',
            $this->getKeyName()
        );
    }

    public function retrieveUserObjects(): HasMany
    {
        return $this->hasMany(
            RetrieveUser::class,
            'object',
            $this->getKeyName()
        );
    }

    public function updateUserSubjects(): HasMany
    {
        return $this->hasMany(
            UpdateUser::class,
            'subject',
            $this->getKeyName()
        );
    }

    public function updateUserObjects(): HasMany
    {
        return $this->hasMany(
            UpdateUser::class,
            'object',
            $this->getKeyName()
        );
    }

    public function deleteUserSubjects(): HasMany
    {
        return $this->hasMany(
            DeleteUser::class,
            'subject',
            $this->getKeyName()
        );
    }

    public function deleteUserObjects(): HasMany
    {
        return $this->hasMany(
            DeleteUser::class,
            'object',
            $this->getKeyName()
        );
    }

    // ---------------------------------------------------------------------------------------------------------------------------------------

    public function privilegesSubjects(): HasMany
    {
        return $this->hasMany(
            Privilege::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }

    public function privilegesObjects(): HasMany
    {
        return $this->hasMany(
            Privilege::class,
            'obejct',
            $this->getKeyName()
        );
    }

    // ---------------------------------------------------------------------------------------------------------------------------------------

    public function createOrderSubjects(): HasMany
    {
        return $this->hasMany(
            CreateOrder::class,
            'subject',
            $this->getKeyName()
        );
    }

    public function createVisitSubjects(): HasMany
    {
        return $this->hasMany(
            CreateVisit::class,
            'subject',
            $this->getKeyName()
        );
    }

    public function retrieveOrderSubjects(): HasMany
    {
        return $this->hasMany(
            RetrieveOrder::class,
            'subject',
            $this->getKeyName()
        );
    }

    public function retrieveVisitSubjects(): HasMany
    {
        return $this->hasMany(
            RetrieveVisit::class,
            'subject',
            $this->getKeyName()
        );
    }

    public function deleteOrderSubjects(): HasMany
    {
        return $this->hasMany(
            DeleteOrder::class,
            'subject',
            $this->getKeyName()
        );
    }

    public function deleteVisitSubjects(): HasMany
    {
        return $this->hasMany(
            DeleteVisit::class,
            'subject',
            $this->getKeyName()
        );
    }
}
