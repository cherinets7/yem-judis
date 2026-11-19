<?php

use App\Enums\ProsecutorReportType;
use App\Enums\ReviewStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prosecutor_weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->enum('report_type', ProsecutorReportType::values());
            $table->unsignedSmallInteger('budget_year_ec');
            $table->unsignedTinyInteger('week_number');
            $table->string('period_covered_ec')->nullable();
            $table->enum('status', ReviewStatus::values())->default(ReviewStatus::Draft->value);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['organization_id', 'user_id', 'report_type', 'budget_year_ec', 'week_number'],
                'pwr_org_user_type_year_week_uniq'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prosecutor_weekly_reports');
    }
};
