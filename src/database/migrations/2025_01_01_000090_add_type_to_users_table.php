<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'type')) {
            Schema::table('users', function (Blueprint $table) {
                // Usar string para compatibilidade com SQLite
                $table->string('type', 20)->default('user')->after('password');
            });

            // Opcional: constraint (cheque) para SQLite/MySQL modernos
            // Ignora se o banco não suportar CHECK
            try {
                DB::statement("ALTER TABLE users ADD CONSTRAINT chk_users_type CHECK (type IN ('admin','user'))");
            } catch (\Throwable $e) {
                // sem pânico; segue sem constraint se não suportar
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
