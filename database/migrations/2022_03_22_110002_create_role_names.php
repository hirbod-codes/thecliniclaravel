<?php

use App\Models\RoleName;
use App\Models\RoleNameGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table;

    public function __construct()
    {
        $this->table = (new RoleName)->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id((new RoleName)->getKeyName());

            $table->string('name')->unique();

            $table->timestamps();
        });

        DB::statement(
            'CREATE TRIGGER before_' . $this->table . '_update BEFORE UPDATE ON ' . $this->table . '
                        FOR EACH ROW
                        BEGIN
                            SIGNAL SQLSTATE \'45000\'
                            SET MESSAGE_TEXT = "Mysql before update trigger";
                        END;'
        );

        DB::statement(
            'CREATE TRIGGER after_' . $this->table . '_delete AFTER DELETE ON ' . $this->table . '
                FOR EACH ROW
                BEGIN
                    IF (EXISTS(SELECT * FROM ' . (new RoleNameGuard)->getTable() . ' WHERE ' . (new RoleNameGuard)->getTable() . '.' . (new RoleNameGuard)->getKeyName() . ' = old.' . (new RoleName)->getKeyName() . ')) = TRUE THEN
                        DELETE FROM ' . (new RoleNameGuard)->getTable() . ' WHERE ' . (new RoleNameGuard)->getTable() . '.' . (new RoleNameGuard)->getKeyName() . ' = old.' . (new RoleName)->getKeyName() . ';
                    END IF;
                END;'
        );
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
