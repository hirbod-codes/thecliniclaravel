<?php

namespace App\Models\Privileges;

use App\Models\Model;
use App\Models\Role;
use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Role $relatedSubject belongsTo
 * @property Role $relatedObject belongsTo
 * @property Business $relatedBusiness belongsTo
 * @property int $subject FK -> Role
 * @property int $object FK -> Role
 * @property int $delete_visit_businesses_business_id FK -> Business
 */
class DeleteVisit extends Model
{
    use HasFactory;

    protected $table = "delete_visit";

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
