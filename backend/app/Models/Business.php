<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $name
 */
class Business extends Model
{
    use HasFactory;

    protected $table = "businesses";
}
