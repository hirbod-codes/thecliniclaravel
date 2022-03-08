<?php

namespace App\Models;

use App\Http\Controllers\CheckAuthentication;
use App\Models\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use TheClinicDataStructures\DataStructures\User\DSUser;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = "users";

    protected $guarded = [
        'id',
        'email_verified_at',
        'phonenumber_verified_at',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phonenumber_verified_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->guarded[] = strtolower(class_basename(Rule::class)) . '_' . (new Rule)->getKey();
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(
            Rule::class,
            strtolower(class_basename(Rule::class)) . '_' . (new Rule)->getKey(),
            (new Rule)->getKey()
        );
    }

    public function getDSUser(): DSUser
    {
        $ruleName = $this->rule()->get()[0]->name;

        $ruleData = [];
        if (in_array($ruleName, get_class_methods(Rule::class))) {
            $ruleData = $this->rule()->{$ruleName}()->get()[0]->toArrayWithoutRelations();
        }

        if ($this->isCustom($ruleName)) {
            return (new DSCustom(...array_merge(
                $this->toArrayWithoutRelations(),
                ['ICheckAuthentication' => new CheckAuthentication, 'ruleName' => $ruleName]
            )))->setData($ruleData);
        } else {
            $dsUser = 'TheClinicDataStructures\\DataStructures\\User\\' . ucfirst($ruleName);
            return new $dsUser(...array_merge(
                $this->toArrayWithoutRelations(),
                $ruleData,
                ['ICheckAuthentication' => new CheckAuthentication]
            ));
        }
    }

    public function isCustom(string $rule): bool
    {
        return !in_array($rule, Rule::$rules);
    }
}
