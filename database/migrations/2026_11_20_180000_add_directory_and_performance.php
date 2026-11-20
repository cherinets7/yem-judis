<?php

use App\Enums\PerformancePeriod;
use App\Enums\ReviewStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('parent_id');
        });

        foreach ([
            'criminal_cases',
            'criminal_appeals',
            'civil_cases',
            'civil_appeals',
            'civil_executions',
            'public_case_trackings',
        ] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('expert_id')->nullable()->after('organization_id')->constrained('users')->nullOnDelete();
            });
        }

        Schema::create('performance_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('period', PerformancePeriod::values());
            $table->unsignedSmallInteger('budget_year_ec');
            $table->unsignedTinyInteger('period_number');
            $table->string('period_label')->nullable();
            $table->text('planned')->nullable();
            $table->text('accomplished')->nullable();
            $table->text('challenges')->nullable();
            $table->enum('status', ReviewStatus::values())->default(ReviewStatus::Draft->value);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['user_id', 'period', 'budget_year_ec', 'period_number'],
                'performance_reports_period_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reports');

        foreach ([
            'criminal_cases',
            'criminal_appeals',
            'civil_cases',
            'civil_appeals',
            'civil_executions',
            'public_case_trackings',
        ] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('expert_id');
            });
        }

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
