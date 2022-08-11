<?php

use App\Helpers\TraitRoleResolver;
use App\Models\Auth\Admin;
use App\Models\Roles\AdminRole;
use Database\Migrations\TraitBaseUserRoleColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use TraitBaseUserRoleColumns, TraitRoleResolver;

    private string $table;

    public function __construct()
    {
        $this->table = (new Admin)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createBaseUserRoleColumns(Admin::class);

        Schema::table($this->table, function (BluePrint $table) {
            $table->unsignedBigInteger((new AdminRole)->getForeignKey());
            $table->foreign((new AdminRole)->getForeignKey(), $this->table . '_' . (new AdminRole)->getTable() . '_' . (new AdminRole)->getForeignKey())
                ->references((new AdminRole)->getKeyName())
                ->on((new AdminRole)->getTable())
                ->onUpdate('restrict')
                ->onDelete('restrict');
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
