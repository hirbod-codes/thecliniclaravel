<?php

namespace Tests\Unit\database\interactions\Privileges;

use App\Models\Privilege;
use App\Models\PrivilegeValue;
use App\Models\Role;
use Database\Interactions\Privileges\DataBaseCreateRole;
use Database\Interactions\Privileges\DataBaseDeleteRole;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;
use TheClinicDataStructures\DataStructures\User\DSUser;

class DataBaseCreateRoleTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testCreateRole(): void
    {
        try {
            DB::beginTransaction();

            foreach (DSUser::$roles as $role) {
                if (Str::contains($role, 'custom')) {
                    continue;
                }

                $roleName = 'my_custom_' . $role;

                (new DataBaseCreateRole)->createRole($roleName, [($privilegeModel = Privilege::first())->name => false]);

                $this->assertDatabaseHas((new Role)->getTable(), [
                    'name' => $roleName
                ]);

                $this->assertDatabaseHas((new PrivilegeValue)->getTable(), [
                    (new Privilege)->getForeignKey() => $privilegeModel->getKey()
                ]);

                sleep(10);
                (new DataBaseDeleteRole)->deleteRole($roleName);
            }
        } finally {
            DB::rollBack();
        }
    }
}
