<?php

namespace Tests\Unit\database\interactions\Privileges;

use App\Models\Privilege;
use App\Models\PrivilegeValue;
use App\Models\Role;
use Database\Interactions\Privileges\Privileges;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;

class PrivilegesTest extends TestCase
{
    use GetAuthenticatables;

    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testSetPrivilege(): void
    {
        DB::beginTransaction();
        try {
            /** @var Role $role */
            $role = Role::query()
                ->where('name', '=', 'admin')
                ->first();

            /** @var PrivilegeValue $privilegeValue */
            $privilegeValue = $this->faker->randomElement($role->privilegeValues);
            $oldValue = $privilegeValue->privilegeValue;

            /** @var Privilege $privilege */
            $privilege = $privilegeValue->privilege;
            $privilegeName = $privilege->name;

            $value = gettype($oldValue) === 'boolean' ? !$oldValue : 'dummy_value';

            $authenticatedRole = $this->getAuthenticatable('admin');
            $dsAuthenticatedRole = $authenticatedRole->getDataStructure();

            (new Privileges)->setPrivilege($dsAuthenticatedRole, $privilegeName, $value);

            $this->assertDatabaseHas($privilegeValue, [
                $role->getForeignKey() => $role->getKey(),
                $privilege->getForeignKey() => $privilege->getKey(),
                'privilegeValue' => $privilegeValue->convertPrivilegeValueToString($value)
            ]);
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            DB::rollBack();

            try {
                $this->assertDatabaseHas($privilegeValue, [
                    $role->getForeignKey() => $role->getKey(),
                    $privilege->getForeignKey() => $privilege->getKey(),
                    'privilegeValue' => $privilegeValue->convertPrivilegeValueToString($oldValue)
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
