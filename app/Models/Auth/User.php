<?php

namespace App\Models\Auth;

use Illuminate\Support\Str;
use App\Auth\CheckAuthentication;
use App\Models\Model;
use App\Models\Order\Order;
use App\Models\Role;
use App\Models\User as ModelsUser;
use App\Models\Visit\Visit;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\DataStructures\Visit\DSVisits;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable,
        Authorizable,
        CanResetPassword,
        MustVerifyEmail;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        $this->guarded[] = 'id';
        $this->guarded[] = 'remember_token';
        $this->guarded[] = 'created_at';
        $this->guarded[] = 'updated_at';

        parent::__construct($attributes);
    }

    public function getDS(): string
    {
        return $this->DS;
    }

    public function getUserRoleNameFKColumnName(): string
    {
        return strtolower(class_basename(ModelsUser::class)) . '_' . (new Role)->getForeignKeyForName();
    }

    public function getDataStructure(array $extraParameters = []): DSUser
    {
        if (static::class === ModelsUser::class || static::class === self::class) {
            throw new \RuntimeException('It\'s not possible to call this method: ' . __FUNCTION__ . 'from class: ' . static::class, 500);
        }

        $userColumns = Schema::getColumnListing((new ModelsUser)->getTable());
        $roleColumns = Schema::getColumnListing($this->getTable());

        $DS = $this->getDS();
        $args = [];
        array_map(function (\ReflectionParameter $parameter) use (&$args, $extraParameters, $userColumns, $roleColumns) {
            $parameterName = $parameter->getName();

            if (array_search($parameterName, array_keys($extraParameters)) !== false) {
                $args[$parameterName] = $extraParameters[$parameterName];
            } else {
                $this->collectDSArgs($args, $parameterName, $userColumns, $roleColumns);
            }
        }, (new \ReflectionClass($DS))->getConstructor()->getParameters());

        return new $DS(...$args);
    }

    protected function collectDSArgs(array &$args, string $parameterName, array $userColumns, array $roleColumns): void
    {
        if (array_search(Str::snake($parameterName), $userColumns) !== false) {
            $this->collectDSArgsFromUser($args, $parameterName);
        } elseif (array_search(Str::snake($parameterName), $roleColumns) !== false) {
            $this->collectDSArgsFromRole($args, $parameterName);
        } else {
            $this->collectOtherDSArgs($args, $parameterName);
        }
    }

    protected function collectDSArgsFromUser(array &$args, string $parameterName): void
    {
        /** @var ModelsUser $user */
        $user = $this->user;

        if ($parameterName === 'id') {
            $args[$parameterName] = $user->getKey();
        } else {
            $args[$parameterName] = $user->{Str::snake($parameterName)};
        }
    }

    protected function collectDSArgsFromRole(array &$args, string $parameterName): void
    {
        if ($parameterName === 'id') {
            $args[$parameterName] = $this->getKey();
        } else {
            $args[$parameterName] = $this->{Str::snake($parameterName)};
        }
    }

    protected function collectOtherDSArgs(array &$args, string $parameterName): void
    {
        /** @var ModelsUser $user */
        $user = $this->user;

        if ($parameterName === 'iCheckAuthentication') {
            $args[$parameterName] = new CheckAuthentication;
        } elseif ($parameterName === 'orders') {
            if ($user->orders === null || empty($user->orders)) {
                $args[$parameterName] = null;
            } else {
                $args[$parameterName] = Order::getMixedDSOrders($user->orders);
            }
        } else {
            // Do nothing for optional arguments.
        }
    }

    /**
     * @param Order[]|Collection $orders
     * @return DSVisits|null
     */
    private function getDSVisits(Collection|array $orders): DSVisits|null
    {
        if (count($orders) === 0) {
            throw new \InvalidArgumentException('$orders variable can not be empty.', 500);
        }

        if ($orders instanceof Collection) {
            $orders = $orders->all();
        }

        $laserVisits = $regularVisits = [];
        foreach ($orders as $order) {
            if ($order->laserOrder !== null) {
                array_merge($laserVisits, ...$order->laserOrder->laserVisits->all());
            } elseif ($order->regularOrder !== null) {
                array_merge($regularVisits, ...$order->regularOrder->regularVisits->all());
            }
        }

        $visits = Visit::getMixeDDSVisits(array_merge($laserVisits, $regularVisits));

        if (count($visits) === 0) {
            return null;
        } else {
            return $visits;
        }
    }

    public function toArrayWithoutRelationsAndRoleRelation(array $excludedColumns = [], bool $excludeForeignKeys = false): array
    {
        $fkColumn = $this->getForeignKeys()[lcfirst(class_basename(Role::class))];

        array_push($excludedColumns, $fkColumn);

        return $this->toArrayWithoutRelations($excludedColumns);
    }

    protected function emailVerifiedAt(): Attribute
    {
        return Attribute::make(get: function ($value) {
            if (gettype($value) === "string") {
                return new \DateTime($value);
            } elseif ($value instanceof Carbon) {
                return new \DateTime($value->toDateTimeString());
            }
        });
    }

    protected function phonenumberVerifiedAt(): Attribute
    {
        return Attribute::make(get: function ($value) {
            if (gettype($value) === "string") {
                return new \DateTime($value);
            } elseif ($value instanceof Carbon) {
                return new \DateTime($value->toDateTimeString());
            }
        });
    }
}
