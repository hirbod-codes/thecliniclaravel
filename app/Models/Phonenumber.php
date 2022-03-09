<?php

namespace App\Models;

use App\Models\rules\Traits\HasAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phonenumber extends Model
{
    use HasFactory, HasAuthenticatable;

    protected $table = "phonenumbers";

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        $this->guarded[] = strtolower(class_basename(static::class)) . '_verified_at';
        $this->casts[strtolower(class_basename(static::class)) . '_verified_at'] = 'datetime';

        parent::__construct($attributes);
    }
}
