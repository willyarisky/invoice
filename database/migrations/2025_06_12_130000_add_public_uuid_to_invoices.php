<?php

use Zero\Lib\DB\Blueprint;
use Zero\Lib\DB\Migration;
use Zero\Lib\DB\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->uuid('public_uuid')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::dropColumnIfExists('invoices', 'public_uuid');
    }
};
