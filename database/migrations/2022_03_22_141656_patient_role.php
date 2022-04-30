<?php

use App\Models\roles\OperatorRole;
use App\Models\roles\PatientRole;
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
        $this->table = (new PatientRole)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createBaseUserRoleColumns($this->table, 'patient');

        Schema::table($this->table, function (BluePrint $table) {
            $fk = (new OperatorRole)->getForeignKey();

            $table->unsignedBigInteger($fk)->nullable();

            $operatorRoleTable = (new OperatorRole)->getTable();
            $table->foreign($fk, $this->table . '_' . $operatorRoleTable . '_' . $fk)
                ->references((new OperatorRole)->getKeyName())
                ->on($operatorRoleTable)
                ->onUpdate('cascade')
                ->onDelete('cascade');

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
