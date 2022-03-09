<?php

namespace App\Models;

use App\Http\Controllers\CheckAuthentication;
use App\Models\Auth\User as Authenticatable;
use App\Models\rules\DSCustom;
use App\Models\rules\Traits\BelongsToRule;
use App\Models\rules\Traits\HasDataStructure;
use App\Models\rules\Traits\HasEmail;
use App\Models\rules\Traits\HasPhonenumber;
use App\Models\rules\Traits\HasUsername;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens,
        HasFactory,
        Notifiable,
        HasEmail,
        HasUsername,
        HasPhonenumber,
        BelongsToRule,
        HasDataStructure;

    protected $table = "users";

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
    protected $casts = [];

    private string $DS = DSCustom::class;

    public function __construct(array $attributes = [])
    {
        $this->guardEmailVerification();
        $this->castEmailVerificationToDatetime();

        $this->guardPhonenumberVerification();
        $this->castPhonenumberVerificationToDatetime();

        $this->guardRuleForeignKey();

        $this->guarded[] = 'id';
        $this->guarded[] = 'remember_token';
        $this->guarded[] = 'created_at';
        $this->guarded[] = 'updated_at';

        parent::__construct($attributes);
    }

    public function getDataStructure(array $additionalArgs = []): DSCustom
    {
        $DS = $this->DS;

        return new $DS(...array_merge(
            $this->toArrayWithoutRelations(),
            $additionalArgs,
            ['ICheckAuthentication' => new CheckAuthentication, 'ruleName' => $this->rule()->first()->name]
        ));
    }
}
