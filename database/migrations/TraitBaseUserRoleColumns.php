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
    
    public function createBaseUserRoleColumns(string $tableName, string $roleName): void
    {
        $fkUserRole = '';
        Schema::create($tableName, function (BluePrint $table) use (&$fkUserRole, $roleName, $tableName) {
            $modelFullname = $this->resolveRuleModelFullname($roleName);

            $table->id((new $modelFullname)->getKeyName());

            $userTable = (new User)->getTable();

            $table->foreign((new $modelFullname)->getKeyName(), $tableName . '_' . $userTable . '_' . (new $modelFullname)->getKeyName())
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
