<?php

namespace App\Models\Roles;

use App\Models\Auth\Admin;
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
 * @property Collection<int, Admin> $userType relation
 * @property int $admin_roles_roles_role_id FK -> Role
 * @property int $admin_roles_role_names_role_name_id FK -> RoleName
 * @property int $admin_roles_roles_guards_roles_guard_id FK -> RoleGuard
 * @property int $admin_roles_role_names_guards_role_names_guard_id FK -> RoleNameGuard
 */
class AdminRole extends Model
{
    use HasFactory;

    protected $table = "admin_roles";

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
            Admin::class,
            $this->getForeignKey(),
            $this->getKeyName()
        );
    }

    public function getUserTypeModelFullname(): string
    {
        return Admin::class;
    }
}
