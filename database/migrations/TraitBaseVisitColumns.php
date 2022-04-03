<?php

namespace Database\Migrations;

use App\Models\Order\LaserOrder;
use App\Models\Visit\Visit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait TraitBaseVisitColumns
{
    public function createBaseVisitColumns(): void
    {
        Schema::create($this->table, function (BluePrint $table) {
            $table->id();

            $visitFK = (new Visit)->getForeignKey();

            $table->unsignedBigInteger($visitFK)->unique();
            $table->foreign($visitFK, $this->table . '_' . (new Visit)->getTable() . '_' . $visitFK)
                ->references((new Visit)->getKeyName())
                ->on((new Visit)->getTable())
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign($visitFK, $this->table . '_visits_guard_' . $visitFK)
                ->references('id')
                ->on('visits_guard')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->unsignedBigInteger('visitTimestamp')->unique();
            $table->integer('consumingTime');
            $table->json('weekDaysPeriods');
            $table->json('dateTimePeriod');

            $table->timestamps();
        });

        DB::statement(
            'CREATE TRIGGER before_' . $this->table . '_insert BEFORE INSERT ON ' . $this->table . '
                        FOR EACH ROW
                        BEGIN
                            INSERT INTO visits_guard (id) VALUES (NEW.' . (new Visit)->getForeignKey() . ');
                        END;'
        );
    }
}
