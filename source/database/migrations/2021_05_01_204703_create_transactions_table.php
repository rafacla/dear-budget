<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->date('deleted_at')->nullable();
            $table->boolean('reconciled')->default(0);
            $table->foreignId('credit_account_id')->nullable()
                ->constrained('accounts')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->foreignId('debit_account_id')->nullable()
                ->constrained('accounts')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->smallInteger('type');
            $table->foreignId('transactions_journal_id')
                ->constrained('transactions_journals')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->decimal('amount', 8, 2);	
            $table->decimal('foreign_amount', 8, 2)->nullable();
            $table->foreignId('foreign_currency_id')
                ->nullable()
                ->constrained('currencies')
                ->onUpdate('RESTRICT')
                ->onDelete('RESTRICT');
            $table->foreignId('subcategory_id')
                ->nullable()
                ->constrained('subcategories')
                ->onUpdate('RESTRICT')
                ->nullOnDelete();
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
        Schema::dropIfExists('transactions');
    }
}
