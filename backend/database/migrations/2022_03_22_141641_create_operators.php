<?php

use App\Helpers\TraitAuthResolver;
use App\Models\Auth\Operator;
use App\Models\Roles\OperatorRole;
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
        $this->table = (new Operator)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createBaseUserRoleColumns(Operator::class);


        Schema::table($this->table, function (BluePrint $table) {
            $table->unsignedBigInteger((new OperatorRole)->getForeignKey());
            $table->foreign((new OperatorRole)->getForeignKey(), $this->table . '_' . (new OperatorRole)->getTable() . '_' . (new OperatorRole)->getForeignKey())
                ->references((new OperatorRole)->getKeyName())
                ->on((new OperatorRole)->getTable())
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
