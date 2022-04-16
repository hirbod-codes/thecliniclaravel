<?php

namespace Tests\Unit\app\Models\roles;

use App\Models\Privilege;
use App\Models\PrivilegeValue;
use App\Models\Role;
use App\Models\roles\CustomRole;
use App\Models\roles\DSCustom;
use App\Models\User;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\VarDumper\Exception\ThrowingCasterException;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use TheClinicDataStructures\DataStructures\User\Interfaces\IPrivilege;

class DSCustomTest extends TestCase
{
    use GetAuthenticatables;

    private Generator $faker;

    /**
     * @var Privilege[]
     */
    private array $someOfPrivileges;

    private string $roleName;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->roleName = 'custom_role';

        $privileges = Privilege::all();
        $this->someOfPrivileges = $this->faker->randomElements($privileges, $this->faker->numberBetween(1, count($privileges)));
    }

    public function testGetPrivilege(): void
    {
        DB::beginTransaction();

        try {
            $userRole = $this->getCustomAuthenticatable($this->roleName);

            /** @var Role $role */
            $role = $userRole->user->role;
            $this->assertEquals($this->roleName, $role->name);

            $dsUser = $userRole->getDataStructure();

            $result = $dsUser->getPrivilege(($privilege = $this->faker->randomElement($this->someOfPrivileges))->name);

            $privilegeValue = PrivilegeValue::query()
                ->where($role->getForeignKey(), '=', $role->getKey())
                ->where($privilege->getForeignKey(), '=', $privilege->getKey())
                ->firstOrFail();

            $this->assertEquals($privilegeValue->privilegeValue, $result);
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            DB::rollBack();

            try {
                $this->assertDatabaseMissing($privilegeValue, [
                    $role->getForeignKey() => $role->getKey(),
                    $privilege->getForeignKey() => $privilege->getKey(),
                ]);
                $this->assertDatabaseMissing($role, ['name' => $this->roleName]);
                $this->assertDatabaseMissing($userRole, [$userRole->getUserRoleNameFKColumnName() => $this->roleName]);
                $this->assertDatabaseMissing($userRole->user, [$role->getForeignKeyForName() => $this->roleName]);
            } catch (\Throwable $th1) {
                if (isset($th)) {
                    throw $th;
                } else {
                    throw $th1;
                }
            }
        }
    }

    public function testPrivilegeExists(): void
    {
        DB::beginTransaction();

        try {
            $userRole = $this->getCustomAuthenticatable($this->roleName);

            /** @var Role $role */
            $role = $userRole->user->role;
            $this->assertEquals($this->roleName, $role->name);

            $dsUser = $userRole->getDataStructure();

            $result = $dsUser->privilegeExists(($privilege = $this->faker->randomElement($this->someOfPrivileges))->name);

            $privilegeValue = PrivilegeValue::query()
                ->where($role->getForeignKey(), '=', $role->getKey())
                ->where($privilege->getForeignKey(), '=', $privilege->getKey())
                ->firstOrFail();

            $this->assertTrue($result);
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            DB::rollBack();

            try {
                $this->assertDatabaseMissing($privilegeValue, [
                    $role->getForeignKey() => $role->getKey(),
                    $privilege->getForeignKey() => $privilege->getKey(),
                ]);
                $this->assertDatabaseMissing($role, ['name' => $this->roleName]);
                $this->assertDatabaseMissing($userRole, [$userRole->getUserRoleNameFKColumnName() => $this->roleName]);
                $this->assertDatabaseMissing($userRole->user, [$role->getForeignKeyForName() => $this->roleName]);
            } catch (\Throwable $th1) {
                if (isset($th)) {
                    throw $th;
                } else {
                    throw $th1;
                }
            }
        }
    }

    public function testGetUserPrivileges(): void
    {
        DB::beginTransaction();

        try {
            $userRole = $this->getCustomAuthenticatable($this->roleName);

            /** @var Role $role */
            $role = $userRole->user->role;
            $this->assertEquals($this->roleName, $role->name);

            $dsUser = $userRole->getDataStructure();

            $result = DSCustom::getUserPrivileges($dsUser->getRuleName());

            $this->assertIsArray($result);
            $this->assertCount(count($role->privilegeValues), $result);

            foreach ($result as $privilegeName => $value) {
                $found = false;
                /** @var PrivilegeValue $privilegeValue */
                foreach ($role->privilegeValues as $privilegeValue) {
                    if ($privilegeValue->privilege->name === $privilegeName) {
                        $found = true;
                        break 1;
                    }
                }
                $this->assertTrue($found);
            }
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            DB::rollBack();

            try {
                $this->assertDatabaseMissing($privilegeValue, [
                    $role->getForeignKey() => $role->getKey(),
                    $privilegeValue->privilege->getForeignKey() => $privilegeValue->privilege->getKey(),
                    'privilegeValue' => $privilegeValue->convertPrivilegeValueToString(true)
                ]);
                $this->assertDatabaseMissing($role, ['name' => $this->roleName]);
                $this->assertDatabaseMissing($userRole, [$userRole->getUserRoleNameFKColumnName() => $this->roleName]);
                $this->assertDatabaseMissing($userRole->user->getTable(), [$role->getForeignKeyForName() => $this->roleName]);
            } catch (\Throwable $th1) {
                if (isset($th)) {
                    throw $th;
                } else {
                    throw $th1;
                }
            }
        }
    }

    public function testSetPrivilege(): void
    {
        DB::beginTransaction();
        try {
            $userRole = $this->getCustomAuthenticatable($this->roleName);

            /** @var Role $role */
            $role = $userRole->user->role;
            $this->assertEquals($this->roleName, $role->name);

            $dsUser = $userRole->getDataStructure();

            $privilege = $this->faker->randomElement($this->someOfPrivileges);
            $privilegeName = $privilege->name;

            /** @var IPrivilege|MockInterface $ip */
            $ip = Mockery::mock(IPrivilege::class);
            $ip
                ->shouldReceive('setPrivilege')
                ->with($dsUser, $privilegeName, true)
                //
            ;

            /** @var Privilege $privilege */
            $result = $dsUser->setPrivilege($privilegeName, true, $ip);
            $this->assertNull($result);
        } finally {
            DB::rollback();
        }
    }

    private function getCustomAuthenticatable(string $roleName): CustomRole
    {
        $role = new Role;
        $role->name = $roleName;
        $role->saveOrFail();

        foreach ($this->someOfPrivileges as $privilege) {
            $privilegeValue = new PrivilegeValue;
            $privilegeValue->{$role->getForeignKey()} = $role->getKey();
            $privilegeValue->{$privilege->getForeignKey()} = $privilege->getKey();
            $privilegeValue->privilegeValue = false;

            $privilegeValue->saveOrFail();
        }

        /** @var User $user */
        $user = User::factory()
            ->usersRolesForeignKey($roleName)
            ->create()
            //
        ;

        $customRole = CustomRole::factory()
            ->usersForeignKey($user->getKey())
            ->usersRoleNameForeignKey($user->{$role->getForeignKeyForName()})
            ->create();

        return $customRole;
    }
}
