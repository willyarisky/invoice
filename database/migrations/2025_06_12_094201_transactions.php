<?php

use Zero\Lib\DB\Blueprint;
use Zero\Lib\DB\Migration;
use Zero\Lib\DB\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['income', 'expense']);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 4, false, 'USD');
            $table->date('date');
            $table->string('description', 255, true);
            $table->enum('source', ['manual', 'invoice'])->default('manual');
            $table->foreignId('vendor_id', true)->constrained('vendors')->onDelete('set null');
            $table->foreignId('invoice_id', true)->constrained('invoices')->onDelete('set null');
            $table->timestamps();
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
