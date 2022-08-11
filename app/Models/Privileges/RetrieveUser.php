<?php

namespace App\Models\Privileges;

use App\Models\Model;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetrieveUser extends Model
{
    use HasFactory;

    protected $table = "retrieve_user";

    public function relatedSubject(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            'subject',
            (new Role)->getKeyName()
        );
    }

    public function relatedObject(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            'object',
            (new Role)->getKeyName()
        );
    }
}