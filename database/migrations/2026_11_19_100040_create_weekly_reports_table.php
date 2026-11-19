<?php

use App\Enums\ReviewStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('work_unit_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('budget_year_ec');
            $table->unsignedTinyInteger('week_number');
            $table->string('week_range_ec')->nullable();
            $table->text('planned_activities')->nullable();
            $table->text('accomplished_activities')->nullable();
            $table->text('challenges')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('status', ReviewStatus::values())->default(ReviewStatus::Draft->value);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['organization_id', 'work_unit_id', 'budget_year_ec', 'week_number'],
                'weekly_reports_org_unit_year_week_uniq'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_reports');
    }
};
