<?php

namespace Database\Migrations;

use App\Models\Email;
use App\Models\Phonenumber;
use App\Models\Rule;
use App\Models\Username;
use Illuminate\Database\Schema\Blueprint;

trait TraitCreateBaseUserColumns
{
    public function createBaseUserColumns(Blueprint $table): void
    {
        $fkRule = strtolower(class_basename(Rule::class)) . '_' . (new Rule)->getKey();

        $table->id();

        $table->unsignedBigInteger($fkRule)->unique();
        $table->foreign($fkRule, 'belongsTo_' . (new Rule)->getTable())
            ->on((new Rule)->getTable())
            ->references((new Rule)->getKey())
            ->onUpdate('cascade')
            ->onDelete('cascade');

        $table->string('firstname');
        $table->string('lastname');

        $table->string(strtolower(class_basename(Username::class)))->unique();
        $table->foreign(strtolower(class_basename(Username::class)), 'belongsTo_' . (new Username)->getTable())
            ->on((new Username)->getTable())
            ->references(strtolower(class_basename(Username::class)))
            ->onUpdate('cascade')
            ->onDelete('cascade');

        $table->string('password');

        $table->string(strtolower(class_basename(Email::class)))->unique()->nullable();
        $table->foreign(strtolower(class_basename(Email::class)), 'belongsTo_' . (new Email)->getTable() . '_' . strtolower(class_basename(Email::class)))
            ->on((new Email)->getTable())
            ->references(strtolower(class_basename(Email::class)))
            ->onUpdate('cascade')
            ->onDelete('cascade');
        $table->string(strtolower(class_basename(Email::class)) . '_verified_at')->nullable();
        $table->foreign(strtolower(class_basename(Email::class)) . '_verified_at', 'belongsTo_' . (new Email)->getTable() . '_' . strtolower(class_basename(Email::class)) . '_verified_at')
            ->on((new Email)->getTable())
            ->references(strtolower(class_basename(Email::class)) . '_verified_at')
            ->onUpdate('cascade')
            ->onDelete('cascade');

        $table->string(strtolower(class_basename(Phonenumber::class)))->unique();
        $table->foreign(strtolower(class_basename(Phonenumber::class)), 'belongsTo_' . (new Phonenumber)->getTable() . '_' . strtolower(class_basename(Phonenumber::class)))
            ->on((new Phonenumber)->getTable())
            ->references(strtolower(class_basename(Phonenumber::class)))
            ->onUpdate('cascade')
            ->onDelete('cascade');
        $table->string(strtolower(class_basename(Phonenumber::class)) . '_verified_at');
        $table->foreign(strtolower(class_basename(Phonenumber::class)) . '_verified_at', 'belongsTo_' . (new Phonenumber)->getTable() . '_' . strtolower(class_basename(Phonenumber::class)) . '_verified_at')
            ->on((new Phonenumber)->getTable())
            ->references(strtolower(class_basename(Phonenumber::class)) . '_verified_at')
            ->onUpdate('cascade')
            ->onDelete('cascade');

        $table->string('gender');

        $table->rememberToken();
    }
}
