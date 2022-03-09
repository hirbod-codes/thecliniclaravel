<?php

use App\Models\Privilege;
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
            $fkPrivilege = strtolower(class_basename(Privilege::class)) . '_' . (new Privilege)->getKeyName();
            $fkRule = strtolower(class_basename(Rule::class)) . '_' . (new Rule)->getKeyName();

            $table->id();

            $table->longText('value');

            $table->unsignedBigInteger($fkPrivilege)->unique();
            $table->foreign($fkPrivilege, 'belongsTo_' . (new Privilege)->getTable())
                ->on((new Privilege)->getTable())
                ->references((new Privilege)->getKeyName())
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedBigInteger($fkRule)->unique();
            $table->foreign($fkRule, 'belongsTo_' . (new Rule)->getTable())
                ->on((new Rule)->getTable())
                ->references((new Rule)->getKeyName())
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
