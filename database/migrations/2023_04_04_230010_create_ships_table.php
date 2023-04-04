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
        Schema::create('ships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('owner');
            $table->string('address');
            $table->string('size');
            $table->string('captain');
            $table->integer('total_member');
            $table->string('image');
            $table->string('licence_number');
            $table->string('permit_document');
            $table->boolean('is_verification')->default(0);
            $table->text('admin_note')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users');
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
        Schema::dropIfExists('ships');
    }
};
