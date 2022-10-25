<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Role;
use App\Models\PrivilegeName;

/**
 * @property Role $role belongsTo
 * @property Role $relatedObject belongsTo
 * @property PrivilegeName $privilegeName belongsTo
 * @property int $role_id FK -> Role
 * @property int $privilege_names_id FK -> PrivilegeName
 * @property int $object FK -> Role
 * @property string $string_value
 * @property integer $integer_value
 * @property boolean $boolean_value
 * @property \DateTime $timestamp_value
 * @property json $jsonvalue
 */
class Privilege extends Model
{
    use HasFactory;

    protected $table = "privileges";

    public function privilegeName(): BelongsTo
    {
        return $this->belongsTo(
            PrivilegeName::class,
            (new PrivilegeName)->getForeignKey(),
            (new PrivilegeName)->getKeyName()
        );
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            (new Role)->getForeignKey(),
            (new Role)->getKeyName()
        );
    }

    public function relatedObject(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            'object',
            (new Role)->getKeyName()
        );
    }
}
