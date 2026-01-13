<?php

use Zero\Lib\DB\Blueprint;
use Zero\Lib\DB\Migration;
use Zero\Lib\DB\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->string('type');
            $table->string('summary');
            $table->text('detail')->nullable();
            $table->string('token')->nullable();
            $table->timestamps();
            $table->index('invoice_id');
            $table->index('type');
            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_events');
    }
};
