<?php

namespace Tests\Unit\database\interactions\Privileges;

use App\Models\Privilege;
use App\Models\PrivilegeValue;
use App\Models\Role;
use Database\Interactions\Privileges\DataBaseCreateRole;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

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
        DB::beginTransaction();
        try {
            $roleName = 'custom_role';
            (new DataBaseCreateRole)->createRole($roleName, [($privilegeModel = Privilege::first())->name, false]);

            $this->assertDatabaseHas((new Role)->getTable(), [
                'name' => $roleName
            ]);

            $this->assertDatabaseHas((new PrivilegeValue)->getTable(), [
                (new Privilege)->getForeignKey() => $privilegeModel->getKey()
            ]);
        } finally {
            DB::rollBack();
        }
    }
}
