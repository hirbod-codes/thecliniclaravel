<?php

namespace Tests\Unit\database\seeders;

use App\Models\Auth\User as Authenticatable;
use App\Models\rules\AdminRule;
use App\Models\rules\DoctorRule;
use App\Models\rules\OperatorRule;
use App\Models\rules\PatientRule;
use App\Models\rules\SecretaryRule;
use App\Models\User;
use Database\Factories\rules\AdminRuleFactory;
use Database\Factories\rules\DoctorRuleFactory;
use Database\Factories\rules\OperatorRuleFactory;
use Database\Factories\rules\PatientRuleFactory;
use Database\Factories\rules\SecretaryRuleFactory;
use Database\Factories\UserFactory;
use Database\Seeders\DatabaseUsersSeeder;
use Database\Traits\ResolveUserModel;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Unit\Traits\GetAuthenticatables;

class DatabaseUsersSeederTest extends TestCase
{
    use ResolveUserModel, GetAuthenticatables;

    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    public function testCreateAdmin(): void
    {
        try {
            DB::beginTransaction();

            $count = $this->faker->numberBetween(1, 5);

            $admins = (new DatabaseUsersSeeder)->createAdmin($count, []);

            $this->assertIsArray($admins);
            $this->assertCount($count, $admins);

            for ($i = 0; $i < $count; $i++) {
                $this->assertInstanceOf(Authenticatable::class, $admins[$i]);
                $this->assertDatabaseHas((new AdminRule)->getTable(), ['username' => $admins[$i]->username]);
            }

            // $attributes username email and phonenumber keys will be removed thats why i copy this variable.
            $attributesCopy = $attributes = (new AdminRuleFactory)->definition();

            $admin = (new DatabaseUsersSeeder)->createAdmin($count, $attributes);

            $this->assertInstanceOf(Authenticatable::class, $admin);
            $this->assertDatabaseHas((new AdminRule)->getTable(), ['username' => $attributesCopy['username']]);

            foreach ($attributesCopy as $attribute => $value) {
                $expected = $value;
                $actual = $admin->{$attribute};

                if (gettype($value) === 'object' && $value instanceof \Datetime) {
                    $expected = $value->format('Y-m-d H:i:s');
                    $actual = $admin->{$attribute}->format('Y-m-d H:i:s');
                }

                $this->assertEquals($expected, $actual);
            }
        } finally {
            DB::rollBack();
        }
    }

    public function testMakeAdmin(): void
    {
        try {
            DB::beginTransaction();

            $count = $this->faker->numberBetween(1, 5);

            $admins = (new DatabaseUsersSeeder)->makeAdmin($count, []);

            $this->assertIsArray($admins);
            $this->assertCount($count, $admins);

            for ($i = 0; $i < $count; $i++) {
                $this->assertInstanceOf(Authenticatable::class, $admins[$i]);
                $this->assertDatabaseMissing((new AdminRule)->getTable(), ['username' => $admins[$i]->username]);
            }

            // $attributes username email and phonenumber keys will be removed thats why i copy this variable.
            $attributesCopy = $attributes = (new AdminRuleFactory)->definition();

            $admin = (new DatabaseUsersSeeder)->makeAdmin($count, $attributes);

            $this->assertInstanceOf(Authenticatable::class, $admin);
            $this->assertDatabaseMissing((new AdminRule)->getTable(), ['username' => $attributesCopy['username']]);

            foreach ($attributesCopy as $attribute => $value) {
                $expected = $value;
                $actual = $admin->{$attribute};

                if (gettype($value) === 'object' && $value instanceof \Datetime) {
                    $expected = $value->format('Y-m-d H:i:s');
                    $actual = $admin->{$attribute}->format('Y-m-d H:i:s');
                }

                $this->assertEquals($expected, $actual);
            }
        } finally {
            DB::rollBack();
        }
    }

    public function testCreateDoctor(): void
    {
        try {
            DB::beginTransaction();

            $count = $this->faker->numberBetween(1, 5);

            $doctors = (new DatabaseUsersSeeder)->createDoctor($count, []);

            $this->assertIsArray($doctors);
            $this->assertCount($count, $doctors);

            for ($i = 0; $i < $count; $i++) {
                $this->assertInstanceOf(Authenticatable::class, $doctors[$i]);
                $this->assertDatabaseHas((new DoctorRule())->getTable(), ['username' => $doctors[$i]->username]);
            }

            // $attributes username email and phonenumber keys will be removed thats why i copy this variable.
            $attributesCopy = $attributes = (new DoctorRuleFactory)->definition();

            $doctor = (new DatabaseUsersSeeder)->createDoctor($count, $attributes);

            $this->assertInstanceOf(Authenticatable::class, $doctor);
            $this->assertDatabaseHas((new DoctorRule)->getTable(), ['username' => $attributesCopy['username']]);

            foreach ($attributesCopy as $attribute => $value) {
                $expected = $value;
                $actual = $doctor->{$attribute};

                if (gettype($value) === 'object' && $value instanceof \Datetime) {
                    $expected = $value->format('Y-m-d H:i:s');
                    $actual = $doctor->{$attribute}->format('Y-m-d H:i:s');
                }

                $this->assertEquals($expected, $actual);
            }
        } finally {
            DB::rollBack();
        }
    }

    public function testMakeDoctor(): void
    {
        try {
            DB::beginTransaction();

            $count = $this->faker->numberBetween(1, 5);

            $doctors = (new DatabaseUsersSeeder)->makeDoctor($count, []);

            $this->assertIsArray($doctors);
            $this->assertCount($count, $doctors);

            for ($i = 0; $i < $count; $i++) {
                $this->assertInstanceOf(Authenticatable::class, $doctors[$i]);
                $this->assertDatabaseMissing((new DoctorRule)->getTable(), ['username' => $doctors[$i]->username]);
            }

            // $attributes username email and phonenumber keys will be removed thats why i copy this variable.
            $attributesCopy = $attributes = (new DoctorRuleFactory)->definition();

            $doctor = (new DatabaseUsersSeeder)->makeDoctor($count, $attributes);

            $this->assertInstanceOf(Authenticatable::class, $doctor);
            $this->assertDatabaseMissing((new DoctorRule)->getTable(), ['username' => $attributesCopy['username']]);

            foreach ($attributesCopy as $attribute => $value) {
                $expected = $value;
                $actual = $doctor->{$attribute};

                if (gettype($value) === 'object' && $value instanceof \Datetime) {
                    $expected = $value->format('Y-m-d H:i:s');
                    $actual = $doctor->{$attribute}->format('Y-m-d H:i:s');
                }

                $this->assertEquals($expected, $actual);
            }
        } finally {
            DB::rollBack();
        }
    }

    public function testCreateSecretary(): void
    {
        try {
            DB::beginTransaction();

            $count = $this->faker->numberBetween(1, 5);

            $secretaries = (new DatabaseUsersSeeder)->createSecretary($count, []);

            $this->assertIsArray($secretaries);
            $this->assertCount($count, $secretaries);

            for ($i = 0; $i < $count; $i++) {
                $this->assertInstanceOf(Authenticatable::class, $secretaries[$i]);
                $this->assertDatabaseHas((new SecretaryRule)->getTable(), ['username' => $secretaries[$i]->username]);
            }

            // $attributes username email and phonenumber keys will be removed thats why i copy this variable.
            $attributesCopy = $attributes = (new SecretaryRuleFactory)->definition();

            $secretary = (new DatabaseUsersSeeder)->createSecretary($count, $attributes);

            $this->assertInstanceOf(Authenticatable::class, $secretary);
            $this->assertDatabaseHas((new SecretaryRule)->getTable(), ['username' => $attributesCopy['username']]);

            foreach ($attributesCopy as $attribute => $value) {
                $expected = $value;
                $actual = $secretary->{$attribute};

                if (gettype($value) === 'object' && $value instanceof \Datetime) {
                    $expected = $value->format('Y-m-d H:i:s');
                    $actual = $secretary->{$attribute}->format('Y-m-d H:i:s');
                }

                $this->assertEquals($expected, $actual);
            }
        } finally {
            DB::rollBack();
        }
    }

    public function testMakeSecretary(): void
    {
        try {
            DB::beginTransaction();

            $count = $this->faker->numberBetween(1, 5);

            $secretaries = (new DatabaseUsersSeeder)->makeSecretary($count, []);

            $this->assertIsArray($secretaries);
            $this->assertCount($count, $secretaries);

            for ($i = 0; $i < $count; $i++) {
                $this->assertInstanceOf(Authenticatable::class, $secretaries[$i]);
                $this->assertDatabaseMissing((new SecretaryRule)->getTable(), ['username' => $secretaries[$i]->username]);
            }

            // $attributes username email and phonenumber keys will be removed thats why i copy this variable.
            $attributesCopy = $attributes = (new SecretaryRuleFactory)->definition();

            $secretary = (new DatabaseUsersSeeder)->makeSecretary($count, $attributes);

            $this->assertInstanceOf(Authenticatable::class, $secretary);
            $this->assertDatabaseMissing((new SecretaryRule)->getTable(), ['username' => $attributesCopy['username']]);

            foreach ($attributesCopy as $attribute => $value) {
                $expected = $value;
                $actual = $secretary->{$attribute};

                if (gettype($value) === 'object' && $value instanceof \Datetime) {
                    $expected = $value->format('Y-m-d H:i:s');
                    $actual = $secretary->{$attribute}->format('Y-m-d H:i:s');
                }

                $this->assertEquals($expected, $actual);
            }
        } finally {
            DB::rollBack();
        }
    }

    public function testCreateOperator(): void
    {
        try {
            DB::beginTransaction();

            $count = $this->faker->numberBetween(1, 5);

            $operators = (new DatabaseUsersSeeder)->createOperator($count, []);

            $this->assertIsArray($operators);
            $this->assertCount($count, $operators);

            for ($i = 0; $i < $count; $i++) {
                $this->assertInstanceOf(Authenticatable::class, $operators[$i]);
                $this->assertDatabaseHas((new OperatorRule)->getTable(), ['username' => $operators[$i]->username]);
            }

            // $attributes username email and phonenumber keys will be removed thats why i copy this variable.
            $attributesCopy = $attributes = (new OperatorRuleFactory)->definition();

            $operator = (new DatabaseUsersSeeder)->createOperator($count, $attributes);

            $this->assertInstanceOf(Authenticatable::class, $operator);
            $this->assertDatabaseHas((new OperatorRule)->getTable(), ['username' => $attributesCopy['username']]);

            foreach ($attributesCopy as $attribute => $value) {
                $expected = $value;
                $actual = $operator->{$attribute};

                if (gettype($value) === 'object' && $value instanceof \Datetime) {
                    $expected = $value->format('Y-m-d H:i:s');
                    $actual = $operator->{$attribute}->format('Y-m-d H:i:s');
                }

                $this->assertEquals($expected, $actual);
            }
        } finally {
            DB::rollBack();
        }
    }

    public function testMakeOperator(): void
    {
        try {
            DB::beginTransaction();

            $count = $this->faker->numberBetween(1, 5);

            $operators = (new DatabaseUsersSeeder)->makeOperator($count, []);

            $this->assertIsArray($operators);
            $this->assertCount($count, $operators);

            for ($i = 0; $i < $count; $i++) {
                $this->assertInstanceOf(Authenticatable::class, $operators[$i]);
                $this->assertDatabaseMissing((new OperatorRule)->getTable(), ['username' => $operators[$i]->username]);
            }

            // $attributes username email and phonenumber keys will be removed thats why i copy this variable.
            $attributesCopy = $attributes = (new OperatorRuleFactory)->definition();

            $operator = (new DatabaseUsersSeeder)->makeOperator($count, $attributes);

            $this->assertInstanceOf(Authenticatable::class, $operator);
            $this->assertDatabaseMissing((new OperatorRule)->getTable(), ['username' => $attributesCopy['username']]);

            foreach ($attributesCopy as $attribute => $value) {
                $expected = $value;
                $actual = $operator->{$attribute};

                if (gettype($value) === 'object' && $value instanceof \Datetime) {
                    $expected = $value->format('Y-m-d H:i:s');
                    $actual = $operator->{$attribute}->format('Y-m-d H:i:s');
                }

                $this->assertEquals($expected, $actual);
            }
        } finally {
            DB::rollBack();
        }
    }

    public function testCreatePatient(): void
    {
        try {
            DB::beginTransaction();

            $count = $this->faker->numberBetween(1, 5);

            $patients = (new DatabaseUsersSeeder)->createPatient($count, []);

            $this->assertIsArray($patients);
            $this->assertCount($count, $patients);

            for ($i = 0; $i < $count; $i++) {
                $this->assertInstanceOf(Authenticatable::class, $patients[$i]);
                $this->assertDatabaseHas((new PatientRule)->getTable(), ['username' => $patients[$i]->username]);
            }

            // $attributes username email and phonenumber keys will be removed thats why i copy this variable.
            $attributesCopy = $attributes = (new PatientRuleFactory)->definition();

            $patient = (new DatabaseUsersSeeder)->createPatient($count, $attributes);

            $this->assertInstanceOf(Authenticatable::class, $patient);
            $this->assertDatabaseHas((new PatientRule)->getTable(), ['username' => $attributesCopy['username']]);

            foreach ($attributesCopy as $attribute => $value) {
                $expected = $value;
                $actual = $patient->{$attribute};

                if (gettype($value) === 'object' && $value instanceof \Datetime) {
                    $expected = $value->format('Y-m-d H:i:s');
                    $actual = $patient->{$attribute}->format('Y-m-d H:i:s');
                }

                $this->assertEquals($expected, $actual);
            }
        } finally {
            DB::rollBack();
        }
    }

    public function testMakePatient(): void
    {
        try {
            DB::beginTransaction();

            $count = $this->faker->numberBetween(1, 5);

            $patients = (new DatabaseUsersSeeder)->makePatient($count, []);

            $this->assertIsArray($patients);
            $this->assertCount($count, $patients);

            for ($i = 0; $i < $count; $i++) {
                $this->assertInstanceOf(Authenticatable::class, $patients[$i]);
                $this->assertDatabaseMissing((new PatientRule)->getTable(), ['username' => $patients[$i]->username]);
            }

            // $attributes username email and phonenumber keys will be removed thats why i copy this variable.
            $attributesCopy = $attributes = (new PatientRuleFactory)->definition();

            $patient = (new DatabaseUsersSeeder)->makePatient($count, $attributes);

            $this->assertInstanceOf(Authenticatable::class, $patient);
            $this->assertDatabaseMissing((new PatientRule)->getTable(), ['username' => $attributesCopy['username']]);

            foreach ($attributesCopy as $attribute => $value) {
                $expected = $value;
                $actual = $patient->{$attribute};

                if (gettype($value) === 'object' && $value instanceof \Datetime) {
                    $expected = $value->format('Y-m-d H:i:s');
                    $actual = $patient->{$attribute}->format('Y-m-d H:i:s');
                }

                $this->assertEquals($expected, $actual);
            }
        } finally {
            DB::rollBack();
        }
    }

    public function testCreateCustom(): void
    {
        try {
            DB::beginTransaction();

            $count = $this->faker->numberBetween(1, 5);

            $customs = (new DatabaseUsersSeeder)->createCustom($count, []);

            $this->assertIsArray($customs);
            $this->assertCount($count, $customs);

            for ($i = 0; $i < $count; $i++) {
                $this->assertInstanceOf(Authenticatable::class, $customs[$i]);
                $this->assertDatabaseHas((new User)->getTable(), ['username' => $customs[$i]->username]);
            }

            // $attributes username email and phonenumber keys will be removed thats why i copy this variable.
            $attributesCopy = $attributes = (new UserFactory)->definition();

            $custom = (new DatabaseUsersSeeder)->createCustom($count, $attributes);

            $this->assertInstanceOf(Authenticatable::class, $custom);
            $this->assertDatabaseHas((new User)->getTable(), ['username' => $attributesCopy['username']]);

            foreach ($attributesCopy as $attribute => $value) {
                $expected = $value;
                $actual = $custom->{$attribute};

                if (gettype($value) === 'object' && $value instanceof \Datetime) {
                    $expected = $value->format('Y-m-d H:i:s');
                    $actual = $custom->{$attribute}->format('Y-m-d H:i:s');
                }

                $this->assertEquals($expected, $actual);
            }
        } finally {
            DB::rollBack();
        }
    }

    public function testMakeCustom(): void
    {
        try {
            DB::beginTransaction();

            $count = $this->faker->numberBetween(1, 5);

            $customs = (new DatabaseUsersSeeder)->makeCustom($count, []);

            $this->assertIsArray($customs);
            $this->assertCount($count, $customs);

            for ($i = 0; $i < $count; $i++) {
                $this->assertInstanceOf(Authenticatable::class, $customs[$i]);
                $this->assertDatabaseMissing((new User)->getTable(), ['username' => $customs[$i]->username]);
            }

            // $attributes username email and phonenumber keys will be removed thats why i copy this variable.
            $attributesCopy = $attributes = (new UserFactory)->definition();

            $custom = (new DatabaseUsersSeeder)->makeCustom($count, $attributes);

            $this->assertInstanceOf(Authenticatable::class, $custom);
            $this->assertDatabaseMissing((new User)->getTable(), ['username' => $attributesCopy['username']]);

            foreach ($attributesCopy as $attribute => $value) {
                $expected = $value;
                $actual = $custom->{$attribute};

                if (gettype($value) === 'object' && $value instanceof \Datetime) {
                    $expected = $value->format('Y-m-d H:i:s');
                    $actual = $custom->{$attribute}->format('Y-m-d H:i:s');
                }

                $this->assertEquals($expected, $actual);
            }
        } finally {
            DB::rollBack();
        }
    }
}
