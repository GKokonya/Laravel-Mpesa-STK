<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stk_payments', function (Blueprint $table) {
            $table->id();
            $table->string('merchantRequestID')->index();
            $table->string('checkoutRequestID')->index();
            $table->foreign('checkoutRequestID')->references('tracking_id')->on('payments');
            $table->string('responseDescription');
            $table->longtext('responseCode');
            $table->string('customerMessage');
            $table->string('status'); //requested , paid , failed
            $table->string('resultCode')->index()->nullable();
            $table->longtext('resultDesc')->nullable();
            $table->float('amount')->nullable();
            $table->string('mpesaReceiptNumber')->nullable()->index();
            $table->string('balance')->nullable();
            $table->datetime('transactionDate')->nullable();
            $table->string('phoneNumber')->nullable();            
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
        Schema::dropIfExists('stk_payments');
    }
};
