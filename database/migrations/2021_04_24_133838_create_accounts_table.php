<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->foreignId('currency_id')->nullable()
                ->constrained('currencies');
            $table->string('number')->nullable();
            $table->string('role');
            $table->smallInteger('statementClosingDay')->nullable();
            $table->smallInteger('statementDueDay')->nullable();
            $table->foreignId('bank_id')->nullable()
                ->constrained('banks')
                    ->onUpdate('CASCADE')
                    ->onDelete('SET NULL');
            $table->foreignId('user_id')
                ->constrained('users')
                    ->onUpdate('CASCADE')
                    ->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
}
