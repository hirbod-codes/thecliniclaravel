<?php

namespace App\Models;

use App\Models\Auth\User as Authenticatable;
use App\Models\Order\Order;
use App\Models\roles\Traits\BelongsToRole;
use Database\Traits\ResolveUserModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory,
        Notifiable,
        ResolveUserModel,
        BelongsToRole;

    protected $table = "users";

    protected string $DS = DSCustom::class;

    public function authenticatableRole(): Authenticatable|null
    {
        foreach ($roles = Role::all() as $role) {
            $modelFullname = $this->resolveRuleModelFullName($role->name);

            if (!class_exists($modelFullname) || !is_subclass_of($modelFullname, Authenticatable::class)) {
                continue;
            }

            if (($authenticatable = $this->hasOne($modelFullname, (new $modelFullname)->getKeyName(), $this->getKeyName())->first()) === null) {
                continue;
            }

            return $authenticatable;
        }

        return null;
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
}
