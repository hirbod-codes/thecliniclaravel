<?php

use App\Models\roles\DoctorRole;
use Database\Migrations\TraitBaseUserRoleColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use TraitBaseUserRoleColumns;

    private string $table;

    public function __construct()
    {
        $this->table = (new DoctorRole)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(null|string $table = null, null|string $roleName = null)
    {
        $this->createBaseUserRoleColumns($table ?: $this->table, $roleName ?: 'doctor');

        Schema::table($this->table, function (BluePrint $table) {
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(string|null $table = null)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists($table ?: $this->table);
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
};
