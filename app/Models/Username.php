<?php

namespace App\Models;

use App\Models\rules\Traits\HasAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Model;

class Username extends Model
{
    use HasFactory, HasAuthenticatable;

    protected $table = "usernames";

    public function __construct(array $attributes = [])
    {
        $this->guarded[] = strtolower(class_basename(static::class)) . '_verified_at';

        parent::__construct($attributes);
    }
}
