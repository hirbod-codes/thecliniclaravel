<?php

namespace Database\Migrations;

use App\Models\Role;
use App\Models\roles\AdminRole;
use App\Models\User;
use Database\Traits\ResolveUserModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait TraitBaseUserRoleColumns
{
    use ResolveUserModel;

    public function createBaseUserRoleColumns(string $tableName, string $roleName, bool $withoutTrigger = false): void
    {
        $fkUserRole = '';
        $modelFullname = $this->resolveRuleModelFullname($roleName);
        $fk = (new $modelFullname)->getKeyName();

        Schema::create($tableName, function (BluePrint $table) use (&$fkUserRole, $fk, $modelFullname, $tableName) {
            $table->id($fk);

            $userTable = (new User)->getTable();

            $table->foreign($fk, $tableName . '_' . $userTable . '_' . $fk)
                ->references((new User)->getKeyName())
                ->on($userTable)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign($fk, $tableName . '_users_guard_' . $fk)
                ->references('id')
                ->on("users_guard")
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $fkUserRole = (new AdminRole)->getUserRoleNameFKColumnName();

            $table->string($fkUserRole);
            $table->foreign($fkUserRole, $tableName . '_' . $userTable . '_' . $fkUserRole)
                ->references((new Role())->getForeignKeyForName())
                ->on($userTable)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamps();
        });

        if ($withoutTrigger) {
            return;
        }

        DB::statement(
            'CREATE TRIGGER before_' . $this->table . '_insert BEFORE INSERT ON ' . $this->table . '
                        FOR EACH ROW
                        BEGIN
                            IF NEW.' . $fkUserRole . ' <> \'' . $roleName . '\' THEN
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
                            IF NEW.' . $fkUserRole . ' <> \'' . $roleName . '\' THEN
                            signal sqlstate \'45000\'
                            SET MESSAGE_TEXT = "Mysql trigger";
                            END IF;
                        END;'
        );
    }
}
