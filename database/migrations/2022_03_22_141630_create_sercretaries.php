<?php

use App\Helpers\TraitAuthResolver;
use App\Models\Auth\Secretary;
use App\Models\Roles\SecretaryRole;
use Database\Migrations\TraitBaseUserRoleColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use TraitBaseUserRoleColumns, TraitAuthResolver;

    private string $table;

    public function __construct()
    {
        $this->table = (new Secretary)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(null|string $table = null, null|string $roleName = null)
    {
        $this->createBaseUserRoleColumns(Secretary::class);

        Schema::table($this->table, function (BluePrint $table) {
            $table->unsignedBigInteger((new SecretaryRole)->getForeignKey());
            $table->foreign((new SecretaryRole)->getForeignKey(), $this->table . '_' . (new SecretaryRole)->getTable() . '_' . (new SecretaryRole)->getForeignKey())
                ->references((new SecretaryRole)->getKeyName())
                ->on((new SecretaryRole)->getTable())
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(string|null $table = null)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists($table ?: $this->table);
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
};
