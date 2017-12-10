<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTableMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('act')->default(false);
            $table->integer('parent_id')->default(0);
            $table->integer('order')->default(0);
            $table->string('path')->default('');
            $table->string('name');
            $table->text('content')->default();
            $table->integer('menuable_id')->default(0);
            $table->string('menuable_type')->default('');
            $table->timestamps();
            $table->softDeletes();

            $table->unique('path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('menu');
    }
}
