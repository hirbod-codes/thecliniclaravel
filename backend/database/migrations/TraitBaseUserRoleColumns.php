<?php

namespace Database\Migrations;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait TraitBaseUserRoleColumns
{
    public function createBaseUserRoleColumns(string $modelFullname, bool $withoutTrigger = false): void
    {
        $tableName = (new $modelFullname)->getTable();
        $fk = (new $modelFullname)->getKeyName();

        Schema::create($tableName, function (BluePrint $table) use ($fk, $tableName) {
            $table->id($fk);

            $userTable = (new User)->getTable();

            $table->unsignedBigInteger((new User)->getForeignKey())->unique();
            $table->foreign((new User)->getForeignKey(), $tableName . '_' . $userTable . '_' . (new User)->getForeignKey())
                ->references((new User)->getKeyName())
                ->on($userTable)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedBigInteger('user_guard_id')->unique();
            $table->foreign('user_guard_id', $tableName . '_users_guard_user_guard_id')
                ->references('id')
                ->on("users_guard")
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->timestamps();
        });

        if ($withoutTrigger) {
            return;
        }

        DB::statement(
            'CREATE TRIGGER before_' . $this->table . '_insert BEFORE INSERT ON ' . $this->table . '
                        FOR EACH ROW
                        BEGIN
                            DECLARE final_id INT;

                            IF (ISNULL(NEW.' . (new User)->getForeignKey() . ')) = TRUE THEN
                                SIGNAL SQLSTATE \'45000\'
                                SET MESSAGE_TEXT = "Mysql before insert trigger NULL foreign key value for column ' . (new User)->getForeignKey() . '";
                            END IF;

                            SET NEW.' . $fk . ' = NEW.' . (new User)->getForeignKey() . ';
                            SET NEW.user_guard_id = NEW.' . (new User)->getForeignKey() . ';
                            SET final_id = NEW.' . (new User)->getForeignKey() . ';

                            IF (EXISTS(SELECT * FROM users_guard WHERE users_guard.id = final_id)) = TRUE THEN
                                SIGNAL SQLSTATE \'45000\'
                                SET MESSAGE_TEXT = "Mysql before insert trigger";
                            END IF;

                            INSERT INTO users_guard(id) VALUES (final_id);
                        END;'
        );

        DB::statement(
            'CREATE TRIGGER before_' . $this->table . '_update BEFORE UPDATE ON ' . $this->table . '
                        FOR EACH ROW
                        BEGIN
                            IF NEW.' . (new User)->getForeignKey() . ' <> OLD.' . (new User)->getForeignKey() . ' OR NEW.user_guard_id <> OLD.user_guard_id OR NEW.' . $fk . ' <> OLD.' . $fk . ' THEN
                                SIGNAL SQLSTATE \'45000\'
                                SET MESSAGE_TEXT = "Mysql before insert trigger";
                            END IF;
                        END;'
        );
    }
}
