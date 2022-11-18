<?php

use App\Models\Business;
use App\Models\Privileges\DeleteOrder;
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
        $this->table = (new DeleteOrder)->getTable();
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

            $table->unsignedBigInteger('subject');
            $table->foreign('subject', $this->table . '_' . (new Role)->getTable() . '_subject')
                ->references((new Role)->getKeyName())
                ->on((new Role)->getTable())
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // can delete

            $table->unsignedBigInteger((new Business)->getForeignKey());
            $table->foreign((new Business)->getForeignKey(), $this->table . '_' . (new Business)->getTable() . '_' . (new Business)->getForeignKey())
                ->references((new Business)->getKeyName())
                ->on((new Business)->getTable())
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // for

            $table->unsignedBigInteger('object')->nullable();
            $table->foreign('object', $this->table . '_' . (new Role)->getTable() . '_object')
                ->references((new Role)->getKeyName())
                ->on((new Role)->getTable())
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->json('details')->nullable();

            $table->timestamps();
        });

        DB::statement(
            'CREATE TRIGGER before_' . $this->table . '_insert BEFORE INSERT ON ' . $this->table . '
                FOR EACH ROW
                BEGIN
                    IF (EXISTS(SELECT * FROM ' . $this->table . ' WHERE ' . $this->table . '.subject = NEW.subject AND ' . $this->table . '.object = NEW.object AND ' . $this->table . '.' . (new Business)->getForeignKey() . ' = NEW.' . (new Business)->getForeignKey() . ')) = TRUE THEN
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
