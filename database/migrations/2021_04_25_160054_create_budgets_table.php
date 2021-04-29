<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('budget_value', $precision = 8, $scale = 2)->default(0);
            $table->decimal('transactions_value', $precision = 8, $scale = 2)->default(0);
            $table->foreignId('subcategory_id')
                ->constrained('subcategories')
                    ->onUpdate('CASCADE')
                    ->onDelete('CASCADE');
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
        Schema::dropIfExists('budgets');
    }
}
