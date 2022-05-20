<?php

namespace App\Models\roles;

use App\Models\Auth\User as Authenticatable;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use TheClinicDataStructures\DataStructures\User\DSCustom;

class CustomRole extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "custom_roles";

    protected string $DS = DSCustom::class;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, $this->getKeyName(), (new User)->getKeyName(), __FUNCTION__);
    }

    protected function collectOtherDSArgs(array &$args, string $parameterName): void
    {
        parent::collectOtherDSArgs($args, $parameterName);

        if ($parameterName === 'roleName') {
            $args[$parameterName] = $this->{$this->getUserRoleNameFKColumnName()};
        } else {
            // Do nothing for optional arguments.
        }
    }

    public function data(): Attribute
    {
        return Attribute::make(get: function (string|null $value) {
            if (is_null($value)) {
                return $value;
            }

            return json_decode($value, true);
        }, set: function (string|array|null $value) {
            if (is_null($value) || is_string($value)) {
                return $value;
            }

            return json_encode($value);
        });
    }
}
