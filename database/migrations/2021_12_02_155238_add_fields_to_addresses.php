<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToAddresses extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->date('date_of_birth')->nullable()->after('url');
            $table->unsignedBigInteger('language_id')->nullable()->after('uuid');
            $table->unsignedBigInteger('country_id')->nullable()->after('language_id');
            $table->string('addition')->nullable()->after('lastname');
            $table->boolean('is_active')->default(true)->after('is_main_address');
            $table->string('department')->nullable()->after('date_of_birth');
            $table->float('latitude')->nullable()->after('addition');
            $table->float('longitude')->nullable()->after('latitude');
            $table->string('mailbox')->nullable()->after('addition');
            $table->string('title')->nullable()->after('company');

            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('language_id')->references('id')->on('languages');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropForeign('addresses_country_id_foreign');
            $table->dropForeign('addresses_language_id_foreign');

            $table->dropColumn([
                'date_of_birth',
                'language_id',
                'addition',
                'is_active',
                'country_id',
                'department',
                'latitude',
                'longitude',
                'mailbox',
                'title',
            ]);
        });
    }
}
