<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTableSite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('act')->default(false);
            $table->integer('parent_id')->default(0);
            $table->integer('order')->default(0);
            $table->string('path')->default('');
            $table->string('view')->default('');
            $table->string('name');
            $table->text('content')->nullable();
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
        Schema::drop('site');
    }
}
