<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $database
 * @property string $table
 * @property string $name
 * @property string $type
 */
class UserColumn extends Model
{
    use HasFactory;

    protected $table = "user_columns";
}
