<?php

namespace Database\Seeders;

use App\Models\PrivilegeName;
use Illuminate\Database\Seeder;

class DatabasePrivilegeNameSeeder extends Seeder
{
    public function run(): void
    {
        (new PrivilegeName(['name' => 'readRoles']))->saveOrFail();
        (new PrivilegeName(['name' => 'writeRoles']))->saveOrFail();
        (new PrivilegeName(['name' => 'editBusinessDefaults']))->saveOrFail();
        (new PrivilegeName(['name' => 'editAvatar']))->saveOrFail();
        (new PrivilegeName(['name' => 'editRegularOrderPrice']))->saveOrFail();
        (new PrivilegeName(['name' => 'editRegularOrderNeededTime']))->saveOrFail();
    }
}
