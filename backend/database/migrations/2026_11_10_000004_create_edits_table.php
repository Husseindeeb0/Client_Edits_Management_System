<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('edits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('priority');
            $table->string('status');
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('is_paid')->default(false);
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('priority');
        });

        // Add CHECK constraints for MySQL 8.0.16+ / MariaDB 10.2.1+
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE `edits` ADD CONSTRAINT `chk_edits_priority` CHECK (`priority` IN ('High', 'Medium', 'Low'))");
            DB::statement("ALTER TABLE `edits` ADD CONSTRAINT `chk_edits_status` CHECK (`status` IN ('Pending', 'Confirmed', 'In Progress', 'Done', 'Cancelled'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edits');
    }
};
