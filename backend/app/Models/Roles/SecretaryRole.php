<?php

namespace App\Models\Roles;

use App\Models\Auth\Secretary;
use App\Models\Model;
use App\Models\Role;
use App\Models\RoleGuard;
use App\Models\RoleName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property Role $role belongsTo
 * @property RoleName $roleName belongsTo
 * @property RoleGuard $roleGuard belongsTo
 * @property Collection<int, Secretary> $userType relation
 */
class SecretaryRole extends Model
{
    use HasFactory;

    protected $table = "secretary_roles";

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
            Secretary::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }

    public function getUserTypeModelFullname(): string
    {
        return Secretary::class;
    }
}
