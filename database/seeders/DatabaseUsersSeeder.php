<?php

namespace Database\Seeders;

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

    private function createAdmin(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $username = Username::factory()->create();
            $email = Email::factory()->create();
            $phonenumber = Phonenumber::factory()->create();
            AdminRule::factory()
                ->for(Rule::where('name', 'admin')->first(), 'rule')
                ->state([
                    strtolower(class_basename(Username::class)) => $username->{strtolower(class_basename(Username::class))},
                    strtolower(class_basename(Email::class)) => $email->{strtolower(class_basename(Email::class))},
                    strtolower(class_basename(Email::class)) . '_verified_at' => $email->{strtolower(class_basename(Email::class)) . '_verified_at'},
                    strtolower(class_basename(Phonenumber::class)) => $phonenumber->{strtolower(class_basename(Phonenumber::class))},
                    strtolower(class_basename(Phonenumber::class)) . '_verified_at' => $phonenumber->{strtolower(class_basename(Phonenumber::class)) . '_verified_at'},
                ]);
        }
    }

    private function createDoctor(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $username = Username::factory()->create();
            $email = Email::factory()->create();
            $phonenumber = Phonenumber::factory()->create();
            DoctorRule::factory()
                ->for(Rule::where('name', 'doctor')->first(), 'rule')
                ->state([
                    strtolower(class_basename(Username::class)) => $username->{strtolower(class_basename(Username::class))},
                    strtolower(class_basename(Email::class)) => $email->{strtolower(class_basename(Email::class))},
                    strtolower(class_basename(Email::class)) . '_verified_at' => $email->{strtolower(class_basename(Email::class)) . '_verified_at'},
                    strtolower(class_basename(Phonenumber::class)) => $phonenumber->{strtolower(class_basename(Phonenumber::class))},
                    strtolower(class_basename(Phonenumber::class)) . '_verified_at' => $phonenumber->{strtolower(class_basename(Phonenumber::class)) . '_verified_at'},
                ]);
        }
    }

    private function createSecretary(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $username = Username::factory()->create();
            $email = Email::factory()->create();
            $phonenumber = Phonenumber::factory()->create();
            SecretaryRule::factory()
                ->for(Rule::where('name', 'secretary')->first(), 'rule')
                ->state([
                    strtolower(class_basename(Username::class)) => $username->{strtolower(class_basename(Username::class))},
                    strtolower(class_basename(Email::class)) => $email->{strtolower(class_basename(Email::class))},
                    strtolower(class_basename(Email::class)) . '_verified_at' => $email->{strtolower(class_basename(Email::class)) . '_verified_at'},
                    strtolower(class_basename(Phonenumber::class)) => $phonenumber->{strtolower(class_basename(Phonenumber::class))},
                    strtolower(class_basename(Phonenumber::class)) . '_verified_at' => $phonenumber->{strtolower(class_basename(Phonenumber::class)) . '_verified_at'},
                ]);
        }
    }

    private function createOperator(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $username = Username::factory()->create();
            $email = Email::factory()->create();
            $phonenumber = Phonenumber::factory()->create();
            OperatorRule::factory()
                ->for(Rule::where('name', 'operator')->first(), 'rule')
                ->state([
                    strtolower(class_basename(Username::class)) => $username->{strtolower(class_basename(Username::class))},
                    strtolower(class_basename(Email::class)) => $email->{strtolower(class_basename(Email::class))},
                    strtolower(class_basename(Email::class)) . '_verified_at' => $email->{strtolower(class_basename(Email::class)) . '_verified_at'},
                    strtolower(class_basename(Phonenumber::class)) => $phonenumber->{strtolower(class_basename(Phonenumber::class))},
                    strtolower(class_basename(Phonenumber::class)) . '_verified_at' => $phonenumber->{strtolower(class_basename(Phonenumber::class)) . '_verified_at'},
                ]);
        }
    }

    private function createPatient(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $username = Username::factory()->create();
            $email = Email::factory()->create();
            $phonenumber = Phonenumber::factory()->create();
            PatientRule::factory()
                ->for(Rule::where('name', 'patient')->first(), 'rule')
                ->state([
                    strtolower(class_basename(Username::class)) => $username->{strtolower(class_basename(Username::class))},
                    strtolower(class_basename(Email::class)) => $email->{strtolower(class_basename(Email::class))},
                    strtolower(class_basename(Email::class)) . '_verified_at' => $email->{strtolower(class_basename(Email::class)) . '_verified_at'},
                    strtolower(class_basename(Phonenumber::class)) => $phonenumber->{strtolower(class_basename(Phonenumber::class))},
                    strtolower(class_basename(Phonenumber::class)) . '_verified_at' => $phonenumber->{strtolower(class_basename(Phonenumber::class)) . '_verified_at'},
                ]);
        }
    }

    private function createCustom(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $username = Username::factory()->create();
            $email = Email::factory()->create();
            $phonenumber = Phonenumber::factory()->create();
            User::factory()
                ->for(Rule::where('name', 'custom')->first(), 'rule')
                ->state([
                    strtolower(class_basename(Username::class)) => $username->{strtolower(class_basename(Username::class))},
                    strtolower(class_basename(Email::class)) => $email->{strtolower(class_basename(Email::class))},
                    strtolower(class_basename(Email::class)) . '_verified_at' => $email->{strtolower(class_basename(Email::class)) . '_verified_at'},
                    strtolower(class_basename(Phonenumber::class)) => $phonenumber->{strtolower(class_basename(Phonenumber::class))},
                    strtolower(class_basename(Phonenumber::class)) . '_verified_at' => $phonenumber->{strtolower(class_basename(Phonenumber::class)) . '_verified_at'},
                ]);
        }
    }
}
