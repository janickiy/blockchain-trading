<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_account', function (Blueprint $table) {
            $table->uuid('account_id')->index();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->string('public_key')->index();
            $table->foreign('public_key')->references('public_key')->on('coins')->onDelete('cascade');
            $table->primary(['account_id', 'public_key']);
            $table->bigInteger('balance')->nullable();
            $table->bigInteger('spent')->nullable();
            $table->bigInteger('sold')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coin_account');
    }
}
