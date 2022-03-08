<?php

use App\Models\Privilege;
use App\Models\PrivilegeValue;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table;

    public function __construct()
    {
        $this->table = (new PrivilegeValue)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $fk = strtolower(class_basename(Privilege::class)) . '_' . (new Privilege)->getKey();

            $table->id();
            $table->unsignedBigInteger($fk)->nullable();

            $table->longText('value');

            $table->foreign($fk, 'belongsTo_' . (new Privilege)->getTable())->on((new Privilege)->getTable())->onUpdate('cascade')->onDelete('cascade');

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
