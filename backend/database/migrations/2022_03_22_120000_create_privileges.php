<?php

use App\Models\Privilege;
use App\Models\PrivilegeName;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table;

    public function __construct()
    {
        $this->table = (new Privilege)->getTable();
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

            $table->unsignedBigInteger((new Role)->getForeignKey());
            $table->foreign((new Role)->getForeignKey(), $this->table . '_' . (new Role)->getTable() . '_' . (new Role)->getForeignKey())
                ->references((new Role)->getKeyName())
                ->on((new Role)->getTable())
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedBigInteger((new PrivilegeName)->getForeignKey());
            $table->foreign((new PrivilegeName)->getForeignKey(), $this->table . '_' . (new PrivilegeName)->getTable() . '_' . (new PrivilegeName)->getForeignKey())
                ->references((new PrivilegeName)->getKeyName())
                ->on((new PrivilegeName)->getTable())
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedBigInteger('object')->nullable();
            $table->foreign('object', $this->table . '_' . (new Role)->getTable() . '_object')
                ->references((new Role)->getKeyName())
                ->on((new Role)->getTable())
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('string_value')->nullable();
            $table->integer('integer_value')->nullable();
            $table->boolean('boolean_value')->nullable();
            $table->timestamp('timestamp_value')->nullable();
            $table->json('json_value')->nullable();

            $table->timestamps();
        });

        DB::statement(
            'CREATE TRIGGER before_' . $this->table . '_insert BEFORE INSERT ON ' . $this->table . '
                FOR EACH ROW
                BEGIN
                    IF (EXISTS(SELECT * FROM ' . $this->table . ' WHERE ' . $this->table . '.' . (new Role)->getForeignKey() . ' = NEW.' . (new Role)->getForeignKey() . ' And ' . $this->table . '.object = NEW.object' . ' And ' . $this->table . '.' . (new PrivilegeName)->getForeignKey() . ' = NEW.' . (new PrivilegeName)->getForeignKey() . ')) = TRUE THEN
                        SIGNAL SQLSTATE \'45000\'
                        SET MESSAGE_TEXT = "Mysql before_' . $this->table . '_insert trigger";
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
