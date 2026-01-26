<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('created_by')->nullable();
            $table->string('title')->nullable();
            $table->enum('platform', ['steam','epic','xbox','playstation','switch','android','ios','itch']);
            $table->char('period_month', 7); // 'YYYY-MM'
            $table->char('currency', 3)->default('USD');
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('fees', 12, 2)->default(0);
            $table->decimal('taxes', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0); // armazenado
            $table->string('statement_ref')->nullable();
            $table->timestamps();

            $table->index(['platform','period_month']);
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('reports');
    }
};
