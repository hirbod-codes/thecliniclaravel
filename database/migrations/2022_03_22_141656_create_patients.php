<?php

use App\Helpers\TraitRoleResolver;
use App\Models\Auth\Operator;
use App\Models\Auth\Patient;
use App\Models\Roles\PatientRole;
use Database\Migrations\TraitBaseUserRoleColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use TraitBaseUserRoleColumns, TraitRoleResolver;

    private string $table;

    public function __construct()
    {
        $this->table = (new Patient)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createBaseUserRoleColumns(Patient::class);

        Schema::table($this->table, function (BluePrint $table) {
            $table->unsignedBigInteger((new PatientRole)->getForeignKey());
            $table->foreign((new PatientRole)->getForeignKey(), $this->table . '_' . (new PatientRole)->getTable() . '_' . (new PatientRole)->getForeignKey())
                ->references((new PatientRole)->getKeyName())
                ->on((new PatientRole)->getTable())
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->unsignedBigInteger((new Operator)->getForeignKey())->nullable();
            $table->foreign((new Operator)->getForeignKey(), $this->table . '_' . (new Operator)->getTable() . '_' . (new Operator)->getForeignKey())
                ->references((new Operator)->getKeyName())
                ->on((new Operator)->getTable())
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('age');

            $table->string('state');
            $table->string('city');
            $table->string('address')->nullable();
            $table->string('laser_grade')->nullable();
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
