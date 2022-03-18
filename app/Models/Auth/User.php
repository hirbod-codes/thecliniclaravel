<?php

namespace App\Models\Auth;

use App\Models\Model;
use App\Models\rules\Traits\BelongsToEmail;
use App\Models\rules\Traits\BelongsToPhonenumber;
use App\Models\rules\Traits\BelongsToRule;
use App\Models\rules\Traits\BelongsToUsername;
use App\Models\rules\Traits\MorphOneEmail;
use App\Models\rules\Traits\MorphOnePhonenumber;
use App\Models\rules\Traits\MorphOneUsername;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable,
        Authorizable,
        CanResetPassword,
        MustVerifyEmail,
        BelongsToEmail,
        BelongsToUsername,
        BelongsToPhonenumber,
        BelongsToRule,
        MorphOneEmail,
        MorphOneUsername,
        MorphOnePhonenumber;

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
        $this->addEmailForeignKey();
        $this->addEmailVerifiedAtForeignKey();
        $this->guardEmailVerification();
        $this->castEmailVerificationToDatetime();

        $this->addPhonenumberForeignKey();
        $this->addPhonenumberVerifiedAtForeignKey();
        $this->guardPhonenumberVerification();
        $this->castPhonenumberVerificationToDatetime();

        $this->addUsernameForeignKey();

        $this->addRuleForeignKey();

        $this->guardRuleForeignKey();

        $this->guarded[] = 'id';
        $this->guarded[] = 'remember_token';
        $this->guarded[] = 'created_at';
        $this->guarded[] = 'updated_at';

        parent::__construct($attributes);
    }
}
