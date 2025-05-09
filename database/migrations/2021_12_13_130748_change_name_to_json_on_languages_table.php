<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeNameToJsonOnLanguagesTable extends Migration
{
    public function up(): void
    {
        Schema::table('languages', function (Blueprint $table): void {
            $table->json('name')->change();
        });

        $this->migrateName();
    }

    public function down(): void
    {
        $this->rollbackName();

        Schema::table('languages', function (Blueprint $table): void {
            $table->string('name')->change();
        });
    }

    private function migrateName(): void
    {
        $languages = DB::table('languages')->get()->toArray();

        array_walk($languages, function (&$item): void {
            $item->name = json_encode([config('app.locale') => $item->name]);
            $item = (array) $item;
        });

        DB::table('languages')->upsert($languages, ['id']);
    }

    private function rollbackName(): void
    {
        $languages = DB::table('languages')->get()->toArray();

        array_walk($languages, function (&$item): void {
            $item->name = substr(json_decode($item->name)->{config('app.locale')}, 0, 255);
            $item = (array) $item;
        });

        DB::table('languages')->upsert($languages, ['id']);
    }
}
