<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('discount_groups', function (Blueprint $table): void {
            $table->string('created_by')->after('created_at')->nullable();
            $table->string('updated_by')->after('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('discount_groups', function (Blueprint $table): void {
            $table->dropColumn(['created_by', 'updated_by']);
        });
    }
};
