<?php

use Zero\Lib\DB\Blueprint;
use Zero\Lib\DB\Migration;
use Zero\Lib\DB\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('currency', 4, true);
        });
    }

    public function down(): void
    {
        Schema::dropColumnIfExists('invoices', 'currency');
    }
};
