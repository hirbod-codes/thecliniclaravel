<?php

namespace Tests\Unit\database\interactions\Accounts;

use App\Models\User;
use Database\Interactions\Accounts\DataBaseUpdateAccount;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use PDOException;
use Tests\TestCase;

/**
 * @covers \Database\Interactions\Accounts\DataBaseUpdateAccount
 */
class DataBaseUpdateAccountTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testMassUpdateAccount()
    {
        try {
            DB::beginTransaction();

            $input = [
                'firstname' => $this->faker->unique()->firstName(),
                'lastname' => $this->faker->unique()->lastName()
            ];

            $updatedUser = (new DataBaseUpdateAccount)->massUpdateAccount($input, [], User::query()->firstOrFail());

            $this->assertInstanceOf(User::class, $updatedUser);
            $this->assertDatabaseHas((new User)->getTable(), ['firstname' => $input['firstname'], 'lastname' => $input['lastname']]);
            $this->assertEquals($input['firstname'], $updatedUser->firstname);
            $this->assertEquals($input['lastname'], $updatedUser->lastname);

            DB::rollBack();

            // Check full name dublication

            DB::beginTransaction();

            $users = User::query()->take(2)->get();
            $input = [
                'firstname' => $users[0]->firstname,
                'lastname' => $users[0]->lastname
            ];

            $this->expectException(QueryException::class);

            $updatedUser = (new DataBaseUpdateAccount)->massUpdateAccount($input, [], $users[1]);

            DB::rollBack();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
