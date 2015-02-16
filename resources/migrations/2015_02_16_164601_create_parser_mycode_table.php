<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateParserMycodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parser_mycode', function (Blueprint $table) {
            $table->increments('cid');
            $table->text('regex');
            $table->text('replacement');
            $table->smallInteger('parseorder', false, true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('parser_mycode');
    }

}
