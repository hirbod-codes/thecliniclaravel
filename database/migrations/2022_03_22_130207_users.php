<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table;

    public function __construct()
    {
        $this->table = (new User)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();

            $roleTable = (new Role)->getTable();
            $fk = (new Role)->getForeignKeyForName();

            $table->string($fk);
            $table->foreign($fk, $this->table . '_' . $roleTable . '_' . $fk)
                ->references('name')
                ->on($roleTable)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('firstname');
            $table->string('lastname');

            $table->string('username')->unique();

            $table->string('password');

            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();

            $table->string('phonenumber')->unique();
            $table->timestamp('phonenumber_verified_at');

            $table->string('gender');

            $table->rememberToken();

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
