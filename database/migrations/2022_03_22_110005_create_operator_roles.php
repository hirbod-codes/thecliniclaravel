<?php

use App\Helpers\TraitAuthResolver;
use App\Models\Role;
use App\Models\RoleGuard;
use App\Models\RoleName;
use App\Models\RoleNameGuard;
use App\Models\Roles\OperatorRole;
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
        $this->table = (new OperatorRole())->getTable();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (BluePrint $table) {
            $table->id((new OperatorRole)->getKeyName());

            $table->unsignedBigInteger((new Role)->getForeignKey())->unique();
            $table->foreign((new Role)->getForeignKey(), $this->table . '_' . (new Role)->getTable() . '_' . (new Role)->getForeignKey())
                ->references((new Role)->getKeyName())
                ->on((new Role)->getTable())
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedBigInteger((new RoleGuard)->getForeignKey())->unique();
            $table->foreign((new RoleGuard)->getForeignKey(), $this->table . '_' . (new RoleGuard)->getTable() . '_' . (new RoleGuard)->getForeignKey())
                ->references((new RoleGuard)->getKeyName())
                ->on((new RoleGuard)->getTable())
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->unsignedBigInteger((new RoleName)->getForeignKey())->unique();
            $table->foreign((new RoleName)->getForeignKey(), $this->table . '_' . (new RoleName)->getTable() . '_' . (new RoleName)->getForeignKey())
                ->references((new RoleName)->getKeyName())
                ->on((new RoleName)->getTable())
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->unsignedBigInteger((new RoleNameGuard)->getForeignKey())->unique();
            $table->foreign((new RoleNameGuard)->getForeignKey(), $this->table . '_' . (new RoleNameGuard)->getTable() . '_' . (new RoleNameGuard)->getForeignKey())
                ->references((new RoleNameGuard)->getKeyName())
                ->on((new RoleNameGuard)->getTable())
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->timestamps();
        });

        DB::statement(
            'CREATE TRIGGER before_' . $this->table . '_insert BEFORE INSERT ON ' . $this->table . '
                        FOR EACH ROW
                        BEGIN
                            DECLARE final_id INT;
                            SET NEW.' . (new OperatorRole)->getKeyName() . ' = NEW.' . (new Role)->getForeignKey() . ';
                            SET NEW.' . (new RoleGuard)->getForeignKey() . ' = NEW.' . (new Role)->getForeignKey() . ';
                            SET NEW.' . (new RoleName)->getForeignKey() . ' = NEW.' . (new Role)->getForeignKey() . ';
                            SET NEW.' . (new RoleNameGuard)->getForeignKey() . ' = NEW.' . (new Role)->getForeignKey() . ';
                            SET final_id = NEW.' . (new Role)->getForeignKey() . ';

                            IF (EXISTS(SELECT * FROM ' . (new RoleGuard)->getTable() . ' WHERE ' . (new RoleGuard)->getTable() . '.' . (new RoleGuard)->getKeyName() . ' = final_id)) = TRUE THEN
                                SIGNAL SQLSTATE \'45000\'
                                SET MESSAGE_TEXT = "Mysql before insert trigger";
                            END IF;

                            IF (EXISTS(SELECT * FROM ' . (new RoleNameGuard)->getTable() . ' WHERE ' . (new RoleNameGuard)->getTable() . '.' . (new RoleNameGuard)->getKeyName() . ' = final_id)) = TRUE THEN
                                SIGNAL SQLSTATE \'45000\'
                                SET MESSAGE_TEXT = "Mysql before insert trigger";
                            END IF;

                            INSERT INTO ' . (new RoleGuard)->getTable() . '(' . (new RoleGuard)->getKeyName() . ') VALUES (final_id);

                            INSERT INTO ' . (new RoleNameGuard)->getTable() . '(' . (new RoleNameGuard)->getKeyName() . ') VALUES (final_id);
                        END;'
        );

        DB::statement(
            'CREATE TRIGGER before_' . $this->table . '_update BEFORE UPDATE ON ' . $this->table . '
                        FOR EACH ROW
                        BEGIN
                            SIGNAL SQLSTATE \'45000\'
                            SET MESSAGE_TEXT = "Mysql before update trigger";
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
