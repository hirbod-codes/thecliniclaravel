<?php

namespace Database\Migrations;

use App\Models\Email;
use App\Models\Phonenumber;
use App\Models\Rule;
use App\Models\Username;
use Illuminate\Database\Schema\Blueprint;

trait TraitCreateBaseUserColumns
{
    public function createBaseUserColumns(Blueprint $table, string $tableName): void
    {
        $table->unsignedBigInteger((new Rule)->getForeignKey());
        $table->foreign((new Rule)->getForeignKey(), $tableName . '_belongsTo_' . (new Rule)->getTable())
            ->references((new Rule)->getKeyName())
            ->on((new Rule)->getTable())
            ->onUpdate('cascade')
            ->onDelete('cascade');

        $table->string('firstname');
        $table->string('lastname');

        $table->string('username')->unique();
        $table->foreign('username', $tableName . '_belongsTo_' . (new Username)->getTable())
            ->on((new Username)->getTable())
            ->references('username')
            ->onUpdate('cascade')
            ->onDelete('cascade');

        $table->string('password');

        $table->string('email')->unique()->nullable();
        $table->foreign('email', $tableName . '_belongsTo_' . (new Email)->getTable() . '_' . 'email')
            ->on((new Email)->getTable())
            ->references('email')
            ->onUpdate('cascade')
            ->onDelete('cascade');
        $table->timestamp('email_verified_at')->nullable();
        $table->foreign('email_verified_at', $tableName . '_belongsTo_' . (new Email)->getTable() . '_' . 'email_verified_at')
            ->on((new Email)->getTable())
            ->references('email_verified_at')
            ->onUpdate('cascade')
            ->onDelete('cascade');

        $table->string('phonenumber')->unique();
        $table->foreign('phonenumber', $tableName . '_belongsTo_' . (new Phonenumber)->getTable() . '_' . 'phonenumber')
            ->on((new Phonenumber)->getTable())
            ->references('phonenumber')
            ->onUpdate('cascade')
            ->onDelete('cascade');
        $table->timestamp('phonenumber_verified_at');
        $table->foreign('phonenumber_verified_at', $tableName . '_belongsTo_' . (new Phonenumber)->getTable() . '_' . 'phonenumber_verified_at')
            ->on((new Phonenumber)->getTable())
            ->references('phonenumber_verified_at')
            ->onUpdate('cascade')
            ->onDelete('cascade');

        $table->string('gender');

        $table->rememberToken();
    }
}
