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
        Schema::create('gift_card', function (Blueprint $table) {
            $table->id();
            $table->double('amount');
            $table->string('status');
            $table->string('claim_link');
            $table->string('egifter_id');
            $table->unsignedBigInteger('owner');
            $table->boolean('pending');
            $table->timestamps();
            $table->foreign('owner')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gift_card');
    }
};
