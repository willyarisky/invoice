<?php

use Zero\Lib\DB\Blueprint;
use Zero\Lib\DB\Migration;
use Zero\Lib\DB\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('tax_id', true)->constrained('taxes')->onDelete('set null');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropColumnIfExists('invoices', 'tax_id');
        Schema::dropColumnIfExists('invoices', 'tax_rate');
        Schema::dropColumnIfExists('invoices', 'tax_amount');
    }
};
