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
        Schema::create('users', function (Blueprint $table) {
            $table->id()->index();
            $table->string('name', 50);
            $table->string('phone', 20)->unique();
            $table->string('email', 50)->unique();
            $table->string('password', 255);
            $table->string('address')->nullable();
            $table->date('dob')->nullable();
            $table->string('avatar')->nullable();
            $table->string('nickname', 50);
            $table->smallInteger('role')->default(2)->comment('UserRoleEnum');
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
        Schema::dropIfExists('users');
    }
};
