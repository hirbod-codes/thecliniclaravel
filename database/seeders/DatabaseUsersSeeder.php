<?php

namespace Database\Seeders;

use App\Models\Auth\User as Authenticatable;
use App\Models\Email;
use App\Models\Phonenumber;
use App\Models\Rule;
use App\Models\rules\AdminRule;
use App\Models\rules\DoctorRule;
use App\Models\rules\OperatorRule;
use App\Models\rules\PatientRule;
use App\Models\rules\SecretaryRule;
use App\Models\User;
use App\Models\Username;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Seeder;

class DatabaseUsersSeeder extends Seeder
{
    public function run(): void
    {
        $this->createAdmin(3);
        $this->createDoctor(2);
        $this->createSecretary(4);
        $this->createOperator(5);
        $this->createPatient(30);
        $this->createCustom(2);
    }

    /**
     * @param integer $count
     * @param array $attributes
     * @return Authenticatable[]|Authenticatable
     */
    public function createAdmin(int $count = 1, array $attributes = []): array|Authenticatable
    {
        if (!empty($attributes)) {
            return $this->makeAdminFactory(
                $this->createUsername($attributes),
                $this->createEmail($attributes),
                $this->createPhonenumber($attributes)
            )->create($attributes);
        }

        $authenticatables = [];
        for ($i = 0; $i < $count; $i++) {
            $authenticatables[] = $this->makeAdminFactory(
                $this->createUsername(),
                $this->createEmail(),
                $this->createPhonenumber()
            )->create();
        }

        return $authenticatables;
    }

    /**
     * @param integer $count
     * @param array $attributes
     * @return Authenticatable[]|Authenticatable
     */
    public function makeAdmin(int $count = 1, array $attributes = []): array|Authenticatable
    {
        if (!empty($attributes)) {
            return $this->makeAdminFactory(
                $this->createUsername($attributes),
                $this->createEmail($attributes),
                $this->createPhonenumber($attributes)
            )->make($attributes);
        }

        $authenticatables = [];
        for ($i = 0; $i < $count; $i++) {
            $authenticatables[] = $this->makeAdminFactory(
                $this->createUsername(),
                $this->createEmail(),
                $this->createPhonenumber()
            )->make();
        }

        return $authenticatables;
    }

    private function makeAdminFactory(Username $username, Email $email, Phonenumber $phonenumber): Factory
    {
        return  AdminRule::factory()
            ->for(Rule::where('name', 'admin')->first(), 'rule')
            ->state([
                'username' => $username->username,
                'email' => $email->email,
                'email_verified_at' => $email->email_verified_at,
                'phonenumber' => $phonenumber->phonenumber,
                'phonenumber_verified_at' => $phonenumber->phonenumber_verified_at,
            ]);
    }

    /**
     * @param integer $count
     * @param array $attributes
     * @return Authenticatable[]|Authenticatable
     */
    public function createDoctor(int $count = 1, array $attributes = []): array|Authenticatable
    {
        if (!empty($attributes)) {
            return $this->makeDoctorFactory(
                $this->createUsername($attributes),
                $this->createEmail($attributes),
                $this->createPhonenumber($attributes)
            )->create($attributes);
        }

        $authenticatables = [];
        for ($i = 0; $i < $count; $i++) {
            $authenticatables[] = $this->makeDoctorFactory(
                $this->createUsername(),
                $this->createEmail(),
                $this->createPhonenumber()
            )->create();
        }

        return $authenticatables;
    }

    /**
     * @param integer $count
     * @param array $attributes
     * @return Authenticatable[]|Authenticatable
     */
    public function makeDoctor(int $count = 1, array $attributes = []): array|Authenticatable
    {
        if (!empty($attributes)) {
            return $this->makeDoctorFactory(
                $this->createUsername($attributes),
                $this->createEmail($attributes),
                $this->createPhonenumber($attributes)
            )->make($attributes);
        }

        $authenticatables = [];
        for ($i = 0; $i < $count; $i++) {
            $authenticatables[] = $this->makeDoctorFactory(
                $this->createUsername(),
                $this->createEmail(),
                $this->createPhonenumber()
            )->make();
        }

        return $authenticatables;
    }

    private function makeDoctorFactory(Username $username, Email $email, Phonenumber $phonenumber): Factory
    {
        return  DoctorRule::factory()
            ->for(Rule::where('name', 'doctor')->first(), 'rule')
            ->state([
                'username' => $username->username,
                'email' => $email->email,
                'email_verified_at' => $email->email_verified_at,
                'phonenumber' => $phonenumber->phonenumber,
                'phonenumber_verified_at' => $phonenumber->phonenumber_verified_at,
            ]);
    }

    /**
     * @param integer $count
     * @param array $attributes
     * @return Authenticatable[]|Authenticatable
     */
    public function createSecretary(int $count = 1, array $attributes = []): array|Authenticatable
    {
        if (!empty($attributes)) {
            return $this->makeSecretaryFactory(
                $this->createUsername($attributes),
                $this->createEmail($attributes),
                $this->createPhonenumber($attributes)
            )->create($attributes);
        }

        $authenticatables = [];
        for ($i = 0; $i < $count; $i++) {
            $authenticatables[] = $this->makeSecretaryFactory(
                $this->createUsername(),
                $this->createEmail(),
                $this->createPhonenumber()
            )->create();
        }

        return $authenticatables;
    }

    /**
     * @param integer $count
     * @param array $attributes
     * @return Authenticatable[]|Authenticatable
     */
    public function makeSecretary(int $count = 1, array $attributes = []): array|Authenticatable
    {
        if (!empty($attributes)) {
            return $this->makeSecretaryFactory(
                $this->createUsername($attributes),
                $this->createEmail($attributes),
                $this->createPhonenumber($attributes)
            )->make($attributes);
        }

        $authenticatables = [];
        for ($i = 0; $i < $count; $i++) {
            $authenticatables[] = $this->makeSecretaryFactory(
                $this->createUsername(),
                $this->createEmail(),
                $this->createPhonenumber()
            )->make();
        }

        return $authenticatables;
    }

    private function makeSecretaryFactory(Username $username, Email $email, Phonenumber $phonenumber): Factory
    {
        return  SecretaryRule::factory()
            ->for(Rule::where('name', 'secretary')->first(), 'rule')
            ->state([
                'username' => $username->username,
                'email' => $email->email,
                'email_verified_at' => $email->email_verified_at,
                'phonenumber' => $phonenumber->phonenumber,
                'phonenumber_verified_at' => $phonenumber->phonenumber_verified_at,
            ]);
    }

    /**
     * @param integer $count
     * @param array $attributes
     * @return Authenticatable[]|Authenticatable
     */
    public function createOperator(int $count = 1, array $attributes = []): array|Authenticatable
    {
        if (!empty($attributes)) {
            return $this->makeOperatorFactory(
                $this->createUsername($attributes),
                $this->createEmail($attributes),
                $this->createPhonenumber($attributes)
            )->create($attributes);
        }

        $authenticatables = [];
        for ($i = 0; $i < $count; $i++) {
            $authenticatables[] = $this->makeOperatorFactory(
                $this->createUsername(),
                $this->createEmail(),
                $this->createPhonenumber()
            )->create();
        }

        return $authenticatables;
    }

    /**
     * @param integer $count
     * @param array $attributes
     * @return Authenticatable[]|Authenticatable
     */
    public function makeOperator(int $count = 1, array $attributes = []): array|Authenticatable
    {
        if (!empty($attributes)) {
            return $this->makeOperatorFactory(
                $this->createUsername($attributes),
                $this->createEmail($attributes),
                $this->createPhonenumber($attributes)
            )->make($attributes);
        }

        $authenticatables = [];
        for ($i = 0; $i < $count; $i++) {
            $authenticatables[] = $this->makeOperatorFactory(
                $this->createUsername(),
                $this->createEmail(),
                $this->createPhonenumber()
            )->make();
        }

        return $authenticatables;
    }

    private function makeOperatorFactory(Username $username, Email $email, Phonenumber $phonenumber): Factory
    {
        return  OperatorRule::factory()
            ->for(Rule::where('name', 'operator')->first(), 'rule')
            ->state([
                'username' => $username->username,
                'email' => $email->email,
                'email_verified_at' => $email->email_verified_at,
                'phonenumber' => $phonenumber->phonenumber,
                'phonenumber_verified_at' => $phonenumber->phonenumber_verified_at,
            ]);
    }

    /**
     * @param integer $count
     * @param array $attributes
     * @return Authenticatable[]|Authenticatable
     */
    public function createPatient(int $count = 1, array $attributes = []): array|Authenticatable
    {
        if (!empty($attributes)) {
            return $this->makePatientFactory(
                $this->createUsername($attributes),
                $this->createEmail($attributes),
                $this->createPhonenumber($attributes)
            )->create($attributes);
        }

        $authenticatables = [];
        for ($i = 0; $i < $count; $i++) {
            $authenticatables[] = $this->makePatientFactory(
                $this->createUsername(),
                $this->createEmail(),
                $this->createPhonenumber()
            )->create();
        }

        return $authenticatables;
    }

    /**
     * @param integer $count
     * @param array $attributes
     * @return Authenticatable[]|Authenticatable
     */
    public function makePatient(int $count = 1, array $attributes = []): array|Authenticatable
    {
        if (!empty($attributes)) {
            return $this->makePatientFactory(
                $this->createUsername($attributes),
                $this->createEmail($attributes),
                $this->createPhonenumber($attributes)
            )->make($attributes);
        }

        $authenticatables = [];
        for ($i = 0; $i < $count; $i++) {
            $authenticatables[] = $this->makePatientFactory(
                $this->createUsername(),
                $this->createEmail(),
                $this->createPhonenumber()
            )->make();
        }

        return $authenticatables;
    }

    private function makePatientFactory(Username $username, Email $email, Phonenumber $phonenumber): Factory
    {
        return  PatientRule::factory()
            ->for(Rule::where('name', 'patient')->first(), 'rule')
            ->state([
                'username' => $username->username,
                'email' => $email->email,
                'email_verified_at' => $email->email_verified_at,
                'phonenumber' => $phonenumber->phonenumber,
                'phonenumber_verified_at' => $phonenumber->phonenumber_verified_at,
            ]);
    }

    /**
     * @param integer $count
     * @param array $attributes
     * @return Authenticatable[]|Authenticatable
     */
    public function createCustom(int $count = 1, array $attributes = []): array|Authenticatable
    {
        if (!empty($attributes)) {
            return $this->makeCustomFactory(
                $this->createUsername($attributes),
                $this->createEmail($attributes),
                $this->createPhonenumber($attributes)
            )->create($attributes);
        }

        $authenticatables = [];
        for ($i = 0; $i < $count; $i++) {
            $authenticatables[] = $this->makeCustomFactory(
                $this->createUsername(),
                $this->createEmail(),
                $this->createPhonenumber()
            )->create();
        }

        return $authenticatables;
    }

    /**
     * @param integer $count
     * @param array $attributes
     * @return Authenticatable[]|Authenticatable
     */
    public function makeCustom(int $count = 1, array $attributes = []): array|Authenticatable
    {
        if (!empty($attributes)) {
            return $this->makeCustomFactory(
                $this->createUsername($attributes),
                $this->createEmail($attributes),
                $this->createPhonenumber($attributes)
            )->make($attributes);
        }

        $authenticatables = [];
        for ($i = 0; $i < $count; $i++) {
            $authenticatables[] = $this->makeCustomFactory(
                $this->createUsername(),
                $this->createEmail(),
                $this->createPhonenumber()
            )->make();
        }

        return $authenticatables;
    }

    private function makeCustomFactory(Username $username, Email $email, Phonenumber $phonenumber): Factory
    {
        return  User::factory()
            ->for(Rule::where('name', 'Custom')->first(), 'rule')
            ->state([
                'username' => $username->username,
                'email' => $email->email,
                'email_verified_at' => $email->email_verified_at,
                'phonenumber' => $phonenumber->phonenumber,
                'phonenumber_verified_at' => $phonenumber->phonenumber_verified_at,
            ]);
    }

    public function createUsername(array &$attributes = []): Username
    {
        return Username::factory()->create($this->makeUsernameAttributes($attributes));
    }

    public function createEmail(array &$attributes = []): Email
    {
        return Email::factory()->create($this->makeEmailAttributes($attributes));
    }

    public function createPhonenumber(array &$attributes = []): Phonenumber
    {
        return Phonenumber::factory()->create($this->makePhonenumberAttributes($attributes));
    }

    public function makeUsername(array &$attributes = []): Username
    {
        return Username::factory()->make($this->makeUsernameAttributes($attributes));
    }

    public function makeEmail(array &$attributes = []): Email
    {
        return Email::factory()->make($this->makeEmailAttributes($attributes));
    }

    public function makePhonenumber(array &$attributes = []): Phonenumber
    {
        return Phonenumber::factory()->make($this->makePhonenumberAttributes($attributes));
    }

    private function makeUsernameAttributes(array &$attributes = []): array
    {
        $usernameAttributes = [];
        if (!empty($attributes)) {
            $usernameAttributes = ['username' => $attributes['username']];
            unset($attributes['username']);
        }

        return $usernameAttributes;
    }

    private function makeEmailAttributes(array &$attributes = []): array
    {
        $emailAttributes = [];
        if (!empty($attributes)) {
            $emailAttributes = [
                'email' => $attributes['email'],
                'email_verified_at' => $attributes['email_verified_at']
            ];
            unset($attributes['email'], $attributes['email_verified_at']);
        }

        return $emailAttributes;
    }

    private function makePhonenumberAttributes(array &$attributes = []): array
    {
        $phonenumberAttributes = [];
        if (!empty($attributes)) {
            $phonenumberAttributes = [
                'phonenumber' => $attributes['phonenumber'],
                'phonenumber_verified_at' => $attributes['phonenumber_verified_at']
            ];
            unset($attributes['phonenumber'], $attributes['phonenumber_verified_at']);
        }

        return $phonenumberAttributes;
    }
}
