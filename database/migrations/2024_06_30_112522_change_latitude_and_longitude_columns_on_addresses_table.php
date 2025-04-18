<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->decimal('latitude', 15, 12)->nullable()->change();
            $table->decimal('longitude', 15, 12)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->decimal('latitude', 8, 6)->nullable()->change();
            $table->decimal('longitude', 9, 6)->nullable()->change();
        });
    }
};
