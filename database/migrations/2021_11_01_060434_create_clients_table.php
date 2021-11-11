<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id('clients_code');
            $table->string('clients_first_name');
            $table->string('clients_last_name');
            $table->string('clients_contact_person');
            $table->text('clients_contact_no');
            $table->string('clients_add1'); 
            $table->string('clients_add2'); 
            $table->string('clients_add3'); 
            $table->string('clients_postcode'); 
            $table->string('clients_city'); 
            $table->string('clients_state'); 
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
        Schema::dropIfExists('clients');
    }
}
