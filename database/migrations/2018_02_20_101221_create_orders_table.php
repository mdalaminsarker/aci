<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_number')->unique();
            $table->integer('outlet_id');
            $table->dateTime('delivery_date');
            $table->integer('delivery_slot_id');
            $table->integer('delivery_status')->default(0);
            $table->integer('delivery_trip_type')->default(0); // regular = 0, return = 1
            $table->string('membership_number')->nullable();
            $table->integer('pos_bill')->nullable();
            $table->integer('payment_method')->nullable(); // 0 = cod , 1 = online
            $table->string('ca_remarks')->nullable();
            $table->integer('availablity_status')->nullable();
            $table->integer('delivery_executive_id')->nullable();
            $table->integer('user_id')->unsigned();
            $table->dateTime('delivered_date')->nullable();
            $table->date('delivered_date_only')->nullable();
            $table->time('delivery_time')->nullable();
            $table->string('attachment')->nullable();
            $table->string('de_remarks')->nullable();
            $table->integer('last_update_user_id')->nullable();
            $table->integer('edit_blocked')->nullable();
            $table->string('follow_up_pending')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('orders');
    }
}
