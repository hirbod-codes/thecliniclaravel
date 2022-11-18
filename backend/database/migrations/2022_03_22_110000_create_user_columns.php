<?php

use App\Models\UserColumn;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table;

    public function __construct()
    {
        $this->table = (new UserColumn)->getTable();
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

            $table->string('database');
            $table->string('table');
            $table->string('name');
            $table->string('type');

            $table->timestamps();
        });

        DB::statement(
            'CREATE TRIGGER before_' . $this->table . '_insert BEFORE INSERT ON ' . $this->table . '
                        FOR EACH ROW
                        BEGIN
                            IF (EXISTS(SELECT * FROM ' . $this->table . ' WHERE ' . $this->table . '.database = NEW.database AND ' . $this->table . '.table = NEW.table AND ' . $this->table . '.name = NEW.name)) = TRUE THEN
                                SIGNAL SQLSTATE \'45000\'
                                SET MESSAGE_TEXT = "Mysql before insert trigger";
                            END IF;
                        END;'
        );

        DB::statement(
            'CREATE TRIGGER before_' . $this->table . '_update BEFORE UPDATE ON ' . $this->table . '
                        FOR EACH ROW
                        BEGIN
                            IF (EXISTS(SELECT * FROM ' . $this->table . ' WHERE ' . $this->table . '.database = NEW.database AND ' . $this->table . '.table = NEW.table AND ' . $this->table . '.name = NEW.name)) = TRUE THEN
                                SIGNAL SQLSTATE \'45000\'
                                SET MESSAGE_TEXT = "Mysql before update trigger";
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
