<?php

namespace App\Models\Roles;

use App\Models\Auth\Doctor;
use App\Models\Model;
use App\Models\Role;
use App\Models\RoleGuard;
use App\Models\RoleName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctorRole extends Model
{
    use HasFactory;

    protected $table = "doctor_roles";

    public function role(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            (new Role)->getForeignKey(),
            (new Role)->getKeyName()
        );
    }

    public function roleName(): BelongsTo
    {
        return $this->belongsTo(
            RoleName::class,
            (new RoleName)->getForeignKey(),
            (new RoleName)->getKeyName()
        );
    }

    public function roleGuard(): BelongsTo
    {
        return $this->belongsTo(
            RoleGuard::class,
            (new RoleGuard)->getForeignKey(),
            (new RoleGuard)->getKeyName()
        );
    }

    public function userType(): HasMany
    {
        return $this->hasMany(
            Doctor::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }

    public function getUserTypeModelFullname(): string
    {
        return Doctor::class;
    }
}
