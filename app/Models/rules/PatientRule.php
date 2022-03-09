<?php

namespace App\Models\rules;

use App\Models\Auth\User as Authenticatable;
use App\Models\rules\Traits\BelongsToRule;
use App\Models\rules\Traits\HasDataStructure;
use App\Models\rules\Traits\HasEmail;
use App\Models\rules\Traits\HasPhonenumber;
use App\Models\rules\Traits\HasUsername;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use TheClinicDataStructures\DataStructures\User\DSPatient;

class PatientRule extends Authenticatable
{
    use HasApiTokens,
        HasFactory,
        Notifiable,
        HasEmail,
        HasUsername,
        HasPhonenumber,
        BelongsToRule,
        HasDataStructure;

    protected $table = "Patient_rule";

    protected $guarded = [];

    private string $DS = DSPatient::class;

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
}
