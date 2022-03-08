<?php

use App\Models\PrivilegeValue;
use App\Models\Rule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table;

    public function __construct()
    {
        $this->table = (new Rule)->getTable() . '_' . (new PrivilegeValue)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $fkRule = strtolower(class_basename(Rule::class)) . '_' . (new Rule)->getKey();
            $fkPrivilege = strtolower(class_basename(PrivilegeValue::class)) . '_' . (new PrivilegeValue())->getKey();

            $table->id();
            $table->unsignedBigInteger($fkRule);
            $table->unsignedBigInteger($fkPrivilege);

            $table->foreign($fkRule, 'belongsToMany_' . (new Rule)->getTable())->on((new Rule)->getTable())->onUpdate('cascade')->onDelete('cascade');
            $table->foreign($fkPrivilege, 'belongsToMany_' . (new PrivilegeValue)->getTable())->on((new PrivilegeValue)->getTable())->onUpdate('cascade')->onDelete('cascade');

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
