<?php

use App\Models\roles\CustomRole;
use App\Models\Role;
use App\Models\roles\AdminRole;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use TheClinicDataStructures\DataStructures\User\DSUser;

return new class extends Migration
{
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
        $fk = (new CustomRole)->getKeyName();
        $fkUserRole = (new AdminRole)->getUserRoleNameFKColumnName();

        Schema::create($this->table, function (BluePrint $table) use ($fk, $fkUserRole) {
            $table->id($fk);

            $table->json('data')->nullable();

            $userTable = (new User)->getTable();

            $table->foreign($fk, $this->table . '_' . $userTable . '_' . $fk)
                ->references((new User)->getKeyName())
                ->on($userTable)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign($fk, $this->table . '_users_guard_' . $fk)
                ->references('id')
                ->on("users_guard")
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string($fkUserRole);
            $table->foreign($fkUserRole, $this->table . '_' . $userTable . '_' . $fkUserRole)
                ->references((new Role)->getForeignKeyForName())
                ->on($userTable)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamps();
        });

        $condition = '';
        for ($i = 0; $i < count(DSUser::$roles); $i++) {
            $condition .= 'NEW.' . $fkUserRole . ' = \'' . DSUser::$roles[$i] . '\' ';
            if ($i !== count(DSUser::$roles) - 1) {
                $condition .= '|| ';
            }
        }

        DB::statement(
            'CREATE TRIGGER before_' . $this->table . '_insert BEFORE INSERT ON ' . $this->table . '
                        FOR EACH ROW
                        BEGIN
                            IF ' . $condition . 'THEN
                            signal sqlstate \'45000\'
                            SET MESSAGE_TEXT = "Mysql trigger";
                            END IF;

                            INSERT INTO users_guard (id) VALUES (NEW.' . $fk . ');
                        END;'
        );

        DB::statement(
            'CREATE TRIGGER before_' . $this->table . '_update BEFORE UPDATE ON ' . $this->table . '
                        FOR EACH ROW
                        BEGIN
                            IF ' . $condition . 'THEN
                            signal sqlstate \'45000\'
                            SET MESSAGE_TEXT = "Mysql trigger";
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
