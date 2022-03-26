<?php

namespace Database\Migrations;

use App\Models\Role;
use App\Models\roles\AdminRole;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait TraitBaseUserRoleColumns
{
    public function createBaseUserRoleColumns(string $tableName, string $roleName): void
    {
        $fkUserRole = '';
        Schema::create($tableName, function (BluePrint $table) use (&$fkUserRole, $tableName) {
            $table->id();

            $fkUser = (new User)->getForeignKey();
            $userTable = (new User)->getTable();

            $table->unsignedBigInteger($fkUser)->unique();
            $table->foreign($fkUser, $tableName . '_' . $userTable . '_' . $fkUser)
                ->references((new User)->getKeyName())
                ->on($userTable)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $fkUserRole = (new AdminRole)->getUserRoleNameFKColumnName();

            $table->string($fkUserRole);
            $table->foreign($fkUserRole, $tableName . '_' . $userTable . '_' . $fkUserRole)
                ->references((new Role())->getForeignKey())
                ->on($userTable)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamps();
        });

        DB::statement(
            'CREATE TRIGGER before_' . $this->table . '_insert BEFORE INSERT ON ' . $this->table . '
                        FOR EACH ROW
                        BEGIN
                            IF NEW.' . $fkUserRole . ' <> \'' . $roleName . '\' THEN
                            signal sqlstate \'45000\'
                            SET MESSAGE_TEXT = "Mysql trigger";
                            END IF;
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
