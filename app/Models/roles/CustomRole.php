<?php

namespace App\Models\roles;

use App\Models\Auth\User as Authenticatable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use TheClinicDataStructures\DataStructures\User\DSUser;

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

    public function getDataStructure(array $extraParameters = []): DSCustom
    {
        $userColumns = Schema::getColumnListing((new User)->getTable());
        $roleColumns = Schema::getColumnListing($this->getTable());

        $args = ['roleName' => $this->{$this->getUserRoleNameFKColumnName()}];
        array_map(function (\ReflectionParameter $parameter) use (&$args, $extraParameters, $userColumns, $roleColumns) {
            $parameterName = $parameter->getName();

            if (array_search($parameterName, array_keys($extraParameters)) !== false) {
                $args[$parameterName] = $extraParameters[$parameterName];
            } else {
                $this->collectDSArgs($args, $parameterName, $userColumns, $roleColumns);
            }
        }, $parameters = (new \ReflectionClass(DSUser::class))->getConstructor()->getParameters());

        $DS = $this->getDS();
        return new $DS(...$args);
    }
}
