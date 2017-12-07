<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreareUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('password');
            $table->string('gender');
            $table->string('intro')->nullable();
            $table->string('icon');
            $table->enum('see', ['所有人', '仅好友', '只有我']);
            $table->enum('modify', ['所有人', '仅好友', '只有我']);
            $table->enum('search', ['所有人', '仅好友', '只有我']);
            $table->enum('info', ['所有人', '仅好友', '只有我']);
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
        //
    }
}
