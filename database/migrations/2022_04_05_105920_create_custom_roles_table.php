<?php

use App\Models\roles\CustomRole;
use App\Models\Role;
use App\Models\roles\AdminRole;
use App\Models\User;
use Database\Migrations\TraitBaseUserRoleColumns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use TheClinicDataStructures\DataStructures\User\DSUser;

return new class extends Migration
{
    use TraitBaseUserRoleColumns;

    private string $table;

    public function __construct()
    {
        $this->table = (new CustomRole)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createBaseUserRoleColumns($this->table, 'custom', withoutTrigger: true);

        Schema::table($this->table, function (BluePrint $table) {
            $table->json('data')->nullable();
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
