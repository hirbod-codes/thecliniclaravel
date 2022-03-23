<?php

namespace App\Models\Auth;

use Illuminate\Support\Str;
use App\Auth\CheckAuthentication;
use App\Models\Model;
use App\Models\roles\Traits\BelongsToRole;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Carbon;
use Laravel\Passport\HasApiTokens;
use TheClinicDataStructures\DataStructures\User\DSUser;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable,
        Authorizable,
        CanResetPassword,
        MustVerifyEmail,
        HasApiTokens,
        BelongsToRole;

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
        $this->addRoleForeignKey();
        $this->guardRoleForeignKey();

        $this->guarded[] = 'id';
        $this->guarded[] = 'remember_token';
        $this->guarded[] = 'created_at';
        $this->guarded[] = 'updated_at';

        parent::__construct($attributes);
    }

    public function getDataStructure(array $additionalArgs = [],): DSUser
    {
        $DS = $this->DS;

        $args = array_merge(
            $this->toArrayWithoutRelationsAndRoleRelation(),
            $additionalArgs,
            ['ICheckAuthentication' => new CheckAuthentication, 'visits' => null, 'orders' => null]
        );

        $formattedArgs = [];
        array_map(function (string $key, $value) use (&$formattedArgs) {
            $formattedArgs[Str::camel($key)] = $value;
        }, array_keys($args), array_values($args));

        return new $DS(...$formattedArgs);
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
