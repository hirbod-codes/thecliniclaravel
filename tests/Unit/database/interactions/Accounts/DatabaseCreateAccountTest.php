<?php

namespace Tests\Unit\database\interactions\Accounts;

use App\Models\Role;
use App\Models\User;
use Database\Interactions\Accounts\DataBaseCreateAccount;
use Database\Traits\ResolveUserModel;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;
use TheClinicDataStructures\DataStructures\User\DSUser;
use Illuminate\Support\Str;

class DatabaseCreateAccountTest extends TestCase
{
    use GetAuthenticatables, ResolveUserModel;

    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testCreateAccount()
    {
        foreach ($this->getAuthenticatables() as $roleName => $authenticatable) {
            try {
                DB::beginTransaction();

                $roleModelFullname = $this->resolveRuleModelFullName($roleName);

                User::factory()->definition();
                /** @var \App\Models\Model $userModel */
                $userModel = User::factory()
                    ->usersRolesForeignKey($roleName)
                    ->make();

                /** @var \App\Models\Model $roleModel */
                $roleModel = new $roleModelFullname;
                $roleModel->{(new User)->getForeignKey()} = $userModel->{(new User)->getKeyName()};
                $roleModel->{$roleModel->getUserRoleNameFKColumnName()} = $userModel->{(new Role)->getForeignKey()};

                $input = array_merge(
                    User::factory()->definition(),
                    $roleModelFullname::factory()->definition(),
                    ['role' => $roleName],
                );

                $dsUser = (new DataBaseCreateAccount)->createAccount($input);
                $dsUserArray = $dsUser->toArray();

                $DSFullname = $this->resolveRuleDataStructureFullName($roleName);

                $this->assertInstanceOf(DSUser::class, $dsUser);
                $this->assertInstanceOf($DSFullname, $dsUser);

                foreach ($authenticatable->getDataStructure()->toArray() as $key => $value) {
                    $this->assertNotFalse(array_search($key, array_keys($dsUserArray)));
                    if (array_search(Str::snake($key), $userModelArray = $userModel->toArray()) !== false) {
                        $this->assertEquals($userModelArray[Str::snake($key)], $dsUserArray[$key]);
                    }
                    // $this->assertEquals($value, $dsUserArray[$key]);
                }

                $this->assertDatabaseHas((new $roleModelFullname)->getTable(), [(new $roleModelFullname)->getKeyName() => $dsUser->getId()]);
                $this->assertDatabaseHas((new User)->getTable(), ['username' => $dsUser->getUsername()]);

                // -------------
                $randomUser = User::first();
                $input['firstname'] = $randomUser->firstname;
                $input['lastname'] = $randomUser->lastname;

                try {
                    $dsUser = (new DataBaseCreateAccount)->createAccount($input);

                    throw new \RuntimeException('Failure!!!');
                } catch (\Throwable $th) {
                    $this->assertEquals(trans_choice('auth.duplicate_fullname', 0), $th->getMessage());
                    $this->assertEquals(422, $th->getCode());
                }

                DB::rollback();
            } catch (\Throwable $th) {
                DB::rollback();
                throw $th;
            }
        }
    }
}
