<?php

use FluxErp\Models\ProjectTask;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->migrateCategorizablesTable();

        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropForeign('project_tasks_category_id_foreign');
            $table->dropColumn('category_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->after('project_id');
        });

        $this->rollbackCategorizablesTable();

        Schema::table('project_tasks', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    private function migrateCategorizablesTable()
    {
        DB::statement('INSERT INTO categorizables(category_id, categorizable_type, categorizable_id)
            SELECT category_id, \'' . trim(
            json_encode(ProjectTask::class, JSON_UNESCAPED_SLASHES), '"'
        ) . '\', id
            FROM project_tasks'
        );
    }

    private function rollbackCategorizablesTable()
    {
        DB::statement('UPDATE project_tasks
            INNER JOIN categorizables
            ON project_tasks.id = categorizables.categorizable_id
            AND categorizables.categorizable_type = \'' . trim(
            json_encode(ProjectTask::class, JSON_UNESCAPED_SLASHES), '"'
        ) . '\'
            SET project_tasks.category_id = categorizables.category_id'
        );
    }
};
