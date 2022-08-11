<?php

namespace App\Models\Privileges;

use App\Models\Model;
use App\Models\Role;
use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreateOrder extends Model
{
    use HasFactory;

    protected $table = "create_order";

    public function relatedSubject(): BelongsTo
    {
        return $this->belongsTo(
            Role::class,
            'subject',
            (new Role)->getKeyName()
        );
    }

    public function relatedBusiness(): BelongsTo
    {
        return $this->belongsTo(
            Business::class,
            (new Business)->getForeignKey(),
            (new Business)->getKeyName()
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
