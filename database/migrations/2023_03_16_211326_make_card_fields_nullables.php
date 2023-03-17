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
        Schema::table('gift_card', function (Blueprint $table) {
            $table->string('claim_link')->unsigned()->nullable()->change();
            $table->string('egifter_id')->unsigned()->nullable()->change();
            $table->string('challenge_code')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gift_card', function (Blueprint $table) {
            $table->dropColumn('claim_link');
            $table->dropColumn('egifter_id');
            $table->dropColumn('challenge_code');
        });
    }
};
