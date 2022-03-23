<?php

namespace Database\Factories\roles;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SecretaryRoleFactory extends Factory
{
    public function definition()
    {
        $user = User::factory()->usersRolesForeignKey('secretary')->create();

        return [
            (new User)->getForeignKey() => $user->{(new User)->getKeyName()},
            strtolower(class_basename(User::class)) . '_' . (new Role)->getForeignKey() => $user->{(new Role)->getForeignKey()}
        ];
    }
}
