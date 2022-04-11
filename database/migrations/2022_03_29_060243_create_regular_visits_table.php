<?php

use App\Models\Order\RegularOrder;
use App\Models\Visit\RegularVisit;
use Database\Migrations\TraitBaseVisitColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use TraitBaseVisitColumns;

    private string $table;

    public function __construct()
    {
        $this->table = (new RegularVisit)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createBaseVisitColumns();

        Schema::table($this->table, function (Blueprint $table) {
            $orderFK = (new RegularOrder)->getForeignKey();

            $table->unsignedBigInteger($orderFK);
            $table->foreign($orderFK, $this->table . '_' . (new RegularOrder)->getTable() . '_' . $orderFK)
                ->references((new RegularOrder)->getKeyName())
                ->on((new RegularOrder)->getTable())
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
