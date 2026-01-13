<?php

use Zero\Lib\DB\Blueprint;
use Zero\Lib\DB\Migration;
use Zero\Lib\DB\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('category_id', true)->constrained('categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropColumnIfExists('transactions', 'category_id');
    }
};
