<?php

use App\Models\Privilege;
use App\Models\PrivilegeValue;
use App\Models\Role;
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
            $fkPrivilege = (new Privilege)->getForeignKey();
            $fkRule = (new Role)->getForeignKey();

            $table->id();

            $table->longText('privilegeValue');

            $table->unsignedBigInteger($fkPrivilege);
            $table->foreign($fkPrivilege, $this->table . '_' . (new Privilege)->getTable() . $fkPrivilege)
                ->on((new Privilege)->getTable())
                ->references((new Privilege)->getKeyName())
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedBigInteger($fkRule);
            $table->foreign($fkRule, $this->table . '_' . (new Role)->getTable() . $fkRule)
                ->on((new Role)->getTable())
                ->references((new Role)->getKeyName())
                ->onUpdate('cascade')
                ->onDelete('cascade');

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
