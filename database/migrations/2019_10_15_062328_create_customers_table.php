<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('code')->unique();
            $table->string('name')->nullable(false);
            $table->string('address1')->nullable(false);
            $table->string('address2')->nullable(true);
            $table->string('city')->nullable(false);
            $table->string('state')->nullable(false);
            $table->integer('zip')->nullable(false);
            $table->string('contact_name')->nullable(false);
            $table->string('contact_phone')->nullable(false);
            $table->string('ext')->nullable(true);
            $table->string('email')->nullable(false);
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
        Schema::dropIfExists('customers');
    }
}
