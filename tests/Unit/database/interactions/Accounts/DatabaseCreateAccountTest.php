<?php

namespace Tests\Unit\database\interactions\Accounts;

use App\Models\Auth\Patient;
use App\Models\Role;
use App\Models\User;
use Database\Interactions\Accounts\DataBaseCreateAccount;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

/**
 * @covers \Database\Interactions\Accounts\DataBaseCreateAccount
 */
class DatabaseCreateAccountTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testCreateAccount()
    {
        try {
            DB::beginTransaction();

            $input = User::factory()->definition();

            $specialInput = Patient::factory()->definition();
            unset($specialInput['laser_grade']);

            Event::fake();

            $user = (new DataBaseCreateAccount)->createAccount('patient', 'patient', $input, $specialInput, null);

            Event::assertDispatched(Registered::class, 1);

            $this->assertInstanceOf(User::class, $user);
            $this->assertEquals($input['username'], $user->username);
            $this->assertDatabaseHas((new User)->getTable(), ['username' => $input['username']]);

            DB::rollback();

            $this->assertDatabaseMissing((new User)->getTable(), ['username' => $input['username']]);
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
