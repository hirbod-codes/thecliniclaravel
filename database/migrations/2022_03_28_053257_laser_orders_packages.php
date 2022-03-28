<?php

use App\Models\Order\LaserOrder;
use App\Models\Order\LaserOrderPackage;
use App\Models\Package\Package;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table;

    public function __construct()
    {
        $this->table = (new LaserOrderPackage)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (BluePrint $table) {
            $table->id();

            $fk = (new LaserOrder)->getForeignKey();

            $table->unsignedBigInteger($fk);
            $table->foreign($fk, $this->table . '_' . (new LaserOrder)->getTable() . '_' . $fk)
                ->references((new LaserOrder)->getKeyName())
                ->on((new LaserOrder)->getTable())
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $fk = (new Package)->getForeignKey();

            $table->unsignedBigInteger($fk);
            $table->foreign($fk, $this->table . '_' . (new Package)->getTable() . '_' . $fk)
                ->references((new Package)->getKeyName())
                ->on((new Package)->getTable())
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->timestamps();
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
