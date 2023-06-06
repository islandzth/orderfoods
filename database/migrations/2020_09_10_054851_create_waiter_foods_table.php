<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWaiterFoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('waiter_foods', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('food_id');
            $table->integer('board_id');
            $table->integer('kitchen_id');
            $table->integer('order_food_id');

            $table->integer('kitchen_quantity')->default(0);
            $table->integer('waiter_quantity')->default(0);
            $table->text('note')->nullable();
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
        Schema::dropIfExists('waiter_foods');
    }
}
