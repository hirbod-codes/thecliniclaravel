<?php

namespace Database\Seeders;

use App\Models\Auth\Admin;
use App\Models\Auth\Doctor;
use App\Models\Auth\Operator;
use App\Models\Auth\Patient;
use App\Models\Auth\Secretary;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\UserColumn;
use Illuminate\Support\Facades\Schema;

class DatabaseUserColumnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $columns = [];
        $columns[] = ['tableName' => (new User)->getTable(), 'columns' => Schema::getColumnListing((new User)->getTable())];
        $columns[] = ['tableName' => (new Admin)->getTable(), 'columns' => Schema::getColumnListing((new Admin)->getTable())];
        $columns[] = ['tableName' => (new Doctor)->getTable(), 'columns' => Schema::getColumnListing((new Doctor)->getTable())];
        $columns[] = ['tableName' => (new Secretary)->getTable(), 'columns' => Schema::getColumnListing((new Secretary)->getTable())];
        $columns[] = ['tableName' => (new Operator)->getTable(), 'columns' => Schema::getColumnListing((new Operator)->getTable())];
        $columns[] = ['tableName' => (new Patient)->getTable(), 'columns' => Schema::getColumnListing((new Patient)->getTable())];

        foreach ($columns as $columnObject) {
            foreach ($columnObject['columns'] as $columnName) {
                (new UserColumn([
                    'database' => env('DB_DATABASE'),
                    'table' => $columnObject['tableName'],
                    'name' => $columnName,
                    'type' => Schema::getColumnType($columnObject['tableName'], $columnName),
                ]))->saveOrFail();
            }
        }
    }
}
