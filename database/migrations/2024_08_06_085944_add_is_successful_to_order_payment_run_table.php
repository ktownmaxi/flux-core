<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_payment_run', function (Blueprint $table): void {
            $table->boolean('success')->default(false)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('order_payment_run', function (Blueprint $table): void {
            $table->dropColumn('success');
        });
    }
};
