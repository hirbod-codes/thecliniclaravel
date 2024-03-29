<?php

use App\Helpers\TraitAuthResolver;
use App\Models\Auth\Doctor;
use App\Models\Roles\DoctorRole;
use Database\Migrations\TraitBaseUserRoleColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use TraitBaseUserRoleColumns, TraitAuthResolver;

    private string $table;

    public function __construct()
    {
        $this->table = (new Doctor)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createBaseUserRoleColumns(Doctor::class);

        Schema::table($this->table, function (BluePrint $table) {
            $table->unsignedBigInteger((new DoctorRole)->getForeignKey());
            $table->foreign((new DoctorRole)->getForeignKey(), $this->table . '_' . (new DoctorRole)->getTable() . '_' . (new DoctorRole)->getForeignKey())
                ->references((new DoctorRole)->getKeyName())
                ->on((new DoctorRole)->getTable())
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists($this->table);
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
};
