<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('payments')->onUpdate('cascade');
            $table->integer('unique_code')->nullable();
            $table->decimal('total', 16, 2);
            $table->dateTime('expired_time')->nullable();
            $table->dateTime('paid_off_time')->nullable();
            $table->integer('order_payment');
            $table->enum('status', ['pending', 'process', 'paid_off', 'expired', 'canceled']);
            $table->string('snap_token', 36)->nullable();
            $table->json('midtrans_notification')->nullable();
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
        Schema::dropIfExists('payments');
    }
}
