<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderFoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_foods', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('food_id');
            $table->integer('board_id');
            $table->integer('kitchen_id');
            $table->integer('user_order_id');
            $table->integer('order_quantity');

            $table->integer('user_kitchen_id')->nullable();
            $table->integer('user_waiter_id')->nullable();
            $table->integer('kitchen_quantity')->default(0);
            $table->integer('waiter_quantity')->default(0);
            $table->integer('status')->default(1);

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
        Schema::dropIfExists('order_foods');
    }
}
