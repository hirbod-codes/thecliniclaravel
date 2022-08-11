<?php

use App\Models\Business;
use App\Models\BusinessDefault;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table;

    public function __construct()
    {
        $this->table = (new BusinessDefault)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger((new Business)->getForeignKey());
            $table->foreign((new Business)->getForeignKey(), $this->table . '_' . (new Business)->getTable() . '_' . (new Business)->getForeignKey())
                ->references((new Business)->getKeyName())
                ->on((new Business)->getTable())
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('min_age');
            $table->integer('visit_alert_deley');
            $table->integer('default_regular_order_price')->nullable();
            $table->integer('default_regular_order_time_consumption')->nullable();

            $table->json('work_schedule');
            $table->json('down_times');
            $table->json('genders');

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
