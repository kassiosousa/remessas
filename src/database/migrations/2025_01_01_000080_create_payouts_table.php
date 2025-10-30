<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();

            $table->char('currency', 3)->default('USD');
            $table->decimal('amount', 12, 2);

            $table->enum('status', ['pending','scheduled','paid','canceled'])->default('pending');
            $table->date('due_date')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->enum('method', ['pix','transfer','paypal','wise','other'])->nullable();

            $table->text('notes')->nullable();

            // Comprovante do pagamento (opcional)
            $table->string('receipt_path')->nullable();            // ex: storage path
            // Nota fiscal do parceiro (opcional)
            $table->string('partner_invoice_number')->nullable();
            $table->string('partner_invoice_path')->nullable();    // ex: storage path

            $table->timestamps();

            $table->index(['report_id','project_id','partner_id'], 'payout_triplet_idx');
        });
    }
    public function down(): void {
        Schema::dropIfExists('payouts');
    }
};
