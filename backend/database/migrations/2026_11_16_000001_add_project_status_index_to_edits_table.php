<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Edits are filtered by project and status together (project edit lists,
     * and the "active edits" check before deleting a client or company).
     */
    public function up(): void
    {
        Schema::table('edits', function (Blueprint $table) {
            $table->index(['project_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('edits', function (Blueprint $table) {
            // MySQL may have dropped its implicit foreign-key index on project_id
            // in favour of the composite one; restore a dedicated index first.
            if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'])) {
                $table->index('project_id');
            }

            $table->dropIndex(['project_id', 'status']);
        });
    }
};
