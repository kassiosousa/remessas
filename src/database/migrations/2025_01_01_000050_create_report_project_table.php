<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('report_project', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->integer('units_sold')->nullable();
            $table->decimal('project_net_amount', 12, 2);    // parte líquida da remessa destinada ao projeto
            $table->char('currency', 3)->default('USD');
            $table->timestamps();

            $table->unique(['project_id','report_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('report_project');
    }
};
