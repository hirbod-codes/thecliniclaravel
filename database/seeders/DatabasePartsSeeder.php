<?php

namespace Database\Seeders;

use App\Models\Part\Part;
use Illuminate\Database\Seeder;

class DatabasePartsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Part::factory()->count(40)->create();
    }
}
