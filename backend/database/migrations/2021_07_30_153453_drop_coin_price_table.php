<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropCoinPriceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('coin_prices');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('coin_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('public_key')->index();
            $table->string('username');
            $table->bigInteger('price')->unsigned();
            $table->timestamps();
        });
    }
}
