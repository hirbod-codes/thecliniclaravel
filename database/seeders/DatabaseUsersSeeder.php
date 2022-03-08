<?php

namespace Database\Seeders;

use App\Models\Rule;
use App\Models\rules\Admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseUsersSeeder extends Seeder
{
    public function run(): void
    {
        $adminRule = Rule::where('name', 'admin')->first();
        // Admin::factory()->for();
        User::factory()->for($adminRule, 'rule')->create();
    }
}
