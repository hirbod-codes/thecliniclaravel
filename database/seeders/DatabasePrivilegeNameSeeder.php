<?php

namespace Database\Seeders;

use App\Models\PrivilegeName;
use Illuminate\Database\Seeder;

class DatabasePrivilegeNameSeeder extends Seeder
{
    public function run(): void
    {
        (new PrivilegeName(['name' => 'editAvatar']))->saveOrFail();
    }
}
