<?php

namespace Database\Migrations;

use App\Models\Order\Order;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait TraitBaseOrderColumns
{
    public function createBaseOrderColumns(): void
    {
        Schema::create($this->table, function (BluePrint $table) {
            $table->id();

            $fk = (new Order)->getForeignKey();

            $table->unsignedBigInteger($fk)->unique();

            $table->foreign($fk, $this->table . '_' . (new Order)->getTable() . '_' . $fk)
            ->references((new Order)->getKeyName())
            ->on((new Order)->getTable())
            ->onDelete('cascade')
            ->onUpdate('cascade');

            $table->foreign($fk, $this->table . '_orders_guard_' . $fk)
            ->references('id')
            ->on('orders_guard')
            ->onDelete('cascade')
            ->onUpdate('cascade');

            $table->integer('price');

            $table->integer('needed_time');

            $table->timestamps();
        });

        DB::statement(
            'CREATE TRIGGER before_' . $this->table . '_insert BEFORE INSERT ON ' . $this->table . '
                        FOR EACH ROW
                        BEGIN
                            INSERT INTO orders_guard (id) VALUES (NEW.' . (new Order)->getForeignKey() . ');
                        END;'
        );
    }
}
