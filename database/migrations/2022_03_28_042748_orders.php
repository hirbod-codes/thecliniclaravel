<?php

use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table;

    public function __construct()
    {
        $this->table = (new Order)->getTable();
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

            $fk = (new User)->getForeignKey();

            $table->unsignedBigInteger($fk);
            $table->foreign($fk, $this->table . '_' . (new User)->getTable() . '_' . $fk)
            ->references((new User)->getKeyName())
            ->on((new User)->getTable())
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
