<?php

use App\Models\Package\Package;
use App\Models\Package\PartPackage;
use App\Models\Part\Part;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table;

    public function __construct()
    {
        $this->table = (new PartPackage)->getTable();
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

            $fk = (new Package)->getForeignKey();

            $table->unsignedBigInteger($fk);
            $table->foreign($fk, $this->table . '_' . (new Package)->getTable() . '_' . $fk)
                ->references((new Package)->getKeyName())
                ->on((new Package)->getTable())
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $fk = (new Part)->getForeignKey();

            $table->unsignedBigInteger($fk);
            $table->foreign($fk, $this->table . '_' . (new Part)->getTable() . '_' . $fk)
                ->references((new Part)->getKeyName())
                ->on((new Part)->getTable())
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
