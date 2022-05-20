<?php

use App\Models\roles\CustomOperatorRole;
use App\Models\roles\CustomPatientRole;
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
        $this->table = (new CustomPatientRole)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createBaseUserRoleColumns($this->table, 'patient', withoutTrigger: true);

        Schema::table($this->table, function (BluePrint $table) {
            $fk = (new CustomOperatorRole)->getForeignKey();

            $table->unsignedBigInteger($fk)->nullable();

            $operatorRoleTable = (new CustomOperatorRole)->getTable();
            // The foreign key's name is too long!
            // $table->foreign($fk, $this->table . '_' . $operatorRoleTable . '_' . $fk)
            $table->foreign($fk, $operatorRoleTable . '_' . $fk)
                ->references((new CustomOperatorRole)->getKeyName())
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
