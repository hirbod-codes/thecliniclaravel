<?php

use App\Models\Role;
use App\Models\Auth\Admin;
use App\Models\Auth\Doctor;
use App\Models\Auth\Operator;
use App\Models\Auth\Patient;
use App\Models\Auth\Secretary;
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
            $table->id((new User)->getKeyName());

            $table->string('firstname');
            $table->string('lastname');

            $table->string('username')->unique();

            $table->string('password');

            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();

            $table->string('phonenumber')->unique();
            $table->timestamp('phonenumber_verified_at')->nullable();

            $table->string('gender');

            $table->rememberToken();

            $table->timestamps();
        });

        DB::statement(
            'CREATE TRIGGER before_' . $this->table . '_insert BEFORE INSERT ON ' . $this->table . '
                        FOR EACH ROW
                        BEGIN
                            IF (EXISTS(SELECT * FROM ' . $this->table . ' WHERE ' . $this->table . '.firstname = NEW.firstname AND ' . $this->table . '.lastname = NEW.lastname)) = TRUE THEN
                                SIGNAL SQLSTATE \'45000\'
                                SET MESSAGE_TEXT = "Mysql before insert trigger";
                            END IF;
                        END;'
        );

        DB::statement(
            'CREATE TRIGGER before_' . $this->table . '_update BEFORE UPDATE ON ' . $this->table . '
                        FOR EACH ROW
                        BEGIN
                            IF OLD.firstname <> NEW.firstname OR OLD.lastname <> NEW.lastname THEN
                                IF (EXISTS(SELECT * FROM ' . $this->table . ' WHERE ' . $this->table . '.firstname = NEW.firstname AND ' . $this->table . '.lastname = NEW.lastname)) = TRUE THEN
                                    SIGNAL SQLSTATE \'45000\'
                                    SET MESSAGE_TEXT = "Mysql before update trigger";
                                END IF;
                            END IF;
                        END;'
        );

        DB::statement(
            'CREATE TRIGGER after_' . $this->table . '_delete AFTER DELETE ON ' . $this->table . '
                FOR EACH ROW
                BEGIN
                    IF (EXISTS(SELECT * FROM users_guard WHERE users_guard.id = old.' . (new User)->getKeyName() . ')) = TRUE THEN
                        DELETE FROM users_guard WHERE users_guard.id = old.' . (new User)->getKeyName() . ';
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
