<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->uuid('created_by')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date_release')->nullable();
            $table->boolean('finished')->default(false);
            $table->string('url')->nullable();
            $table->unsignedBigInteger('steam_id')->nullable();
            $table->string('capsule')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('projects');
    }
};
