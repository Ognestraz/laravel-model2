<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImagesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function(Blueprint $table)
        {        
            $table->increments('id');
            $table->boolean('act')->default(false);
            $table->integer('user_id')->default(0);
            $table->string('path');
            $table->integer('order')->default(0);
            $table->string('part', 32)->default('');
            $table->integer('imageable_id')->default(0);
            $table->string('imageable_type')->default('');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('images');
    }
}
