<?php

namespace Tests\Unit\database\interactions\Privileges;

use App\Models\Role;
use Database\Interactions\Privileges\DataBaseDeleteRole;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DataBaseDeleteRoleTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testDeleteRole(): void
    {
        DB::beginTransaction();
        try {
            $role = $this->faker->randomElement(Role::all());

            $roleName = $role->name;

            (new DataBaseDeleteRole)->deleteRole($roleName);

            $this->assertDatabaseMissing((new Role)->getTable(), [
                'name' => $roleName
            ]);
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            DB::rollBack();

            try {
                $this->assertDatabaseHas((new Role)->getTable(), [
                    'name' => $roleName
                ]);
            } catch (\Throwable $th1) {
                if (isset($th)) {
                    throw $th;
                } else {
                    throw $th1;
                }
            }
        }
    }
}