<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->uuid('created_by')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('portfolio')->nullable();
            $table->date('birthday')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('partners');
    }
};
