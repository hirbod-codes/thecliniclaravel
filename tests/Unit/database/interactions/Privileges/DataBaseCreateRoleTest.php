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
use App\PoliciesLogicDataStructures\DataStructures\User\DSUser;

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
            foreach (DSUser::$roles as $role) {
                DB::beginTransaction();

                if (!Str::contains($role, 'custom')) {
                    continue;
                }

                $roleName = $this->faker->lexify();

                (new DataBaseCreateRole)->createRole($roleName, [($privilegeModel = Privilege::first())->name => false], $role);

                $this->assertDatabaseHas((new Role)->getTable(), [
                    'name' => $roleName
                ]);

                $this->assertDatabaseHas((new PrivilegeValue)->getTable(), [
                    (new Privilege)->getForeignKey() => $privilegeModel->getKey()
                ]);

                (new DataBaseDeleteRole)->deleteRole($roleName);

                DB::rollBack(DB::transactionLevel());
            }
        } catch (\Throwable $th) {
            DB::rollBack(DB::transactionLevel());
            throw $th;
        }
    }
}
