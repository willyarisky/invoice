<?php

use Zero\Lib\DB\Blueprint;
use Zero\Lib\DB\Migration;
use Zero\Lib\DB\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->text('address')->nullable();
            $table->timestamps();
            $table->unique('email');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('invoice_no');
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['draft', 'sent', 'paid'])->default('draft');
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
            $table->unique('invoice_no');
            $table->index('client_id');
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->string('description');
            $table->integer('qty')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
            $table->index('invoice_id');
        });

        Schema::create('admin', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password_hash');
            $table->datetime('last_login', true);
            $table->timestamps();
            $table->unique('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('admin');
    }
};
