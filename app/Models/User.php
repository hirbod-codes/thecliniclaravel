<?php

namespace App\Models;

use App\Models\Auth\User as Authenticatable;
use App\Models\rules\DSCustom;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use HasFactory,
        Notifiable;

    protected $table = "users";

    protected string $DS = DSCustom::class;

    public function authenticatableRole(): Authenticatable|null
    {
        foreach ($allModelsFullnames = $this->getAllModelsFullname() as $modelFullname) {
            if (array_search($this->getForeignKey(), Schema::getColumnListing((new $modelFullname)->getTable())) === false) {
                continue;
            }

            if (($authenticatable = $this->hasOne($modelFullname, $this->getForeignKey(), $this->getKeyName())->first()) === null) {
                continue;
            }

            return $authenticatable;
        }

        return null;
    }
}
