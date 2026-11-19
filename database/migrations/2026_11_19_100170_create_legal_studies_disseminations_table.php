<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Legal studies and dissemination register. Code 011.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_studies_disseminations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('budget_year_ec');
            $table->text('needs_assessment_topic')->nullable();
            $table->unsignedInteger('manuals_prepared')->default(0);
            $table->unsignedInteger('brochures_prepared')->default(0);
            $table->unsignedInteger('awareness_platforms_count')->default(0);
            $table->unsignedInteger('reached_kebeles_cities')->default(0);
            $table->unsignedInteger('media_awareness_male')->default(0);
            $table->unsignedInteger('media_awareness_female')->default(0);
            $table->unsignedInteger('media_awareness_total')->default(0);
            $table->unsignedInteger('reached_schools')->default(0);
            $table->unsignedInteger('legal_advice_institutions')->default(0);
            $table->unsignedInteger('legal_advice_individuals')->default(0);
            $table->unsignedInteger('legal_advice_total')->default(0);
            $table->unsignedInteger('total_citizens_male')->default(0);
            $table->unsignedInteger('total_citizens_female')->default(0);
            $table->unsignedInteger('total_citizens_sum')->default(0);
            $table->unsignedInteger('collected_proclamations')->default(0);
            $table->unsignedInteger('collected_regulations')->default(0);
            $table->unsignedInteger('collected_directives')->default(0);
            $table->unsignedInteger('collected_total')->default(0);
            $table->unsignedInteger('disseminated_proclamations')->default(0);
            $table->unsignedInteger('disseminated_regulations')->default(0);
            $table->unsignedInteger('disseminated_directives')->default(0);
            $table->unsignedInteger('disseminated_total')->default(0);
            $table->unsignedInteger('drafted_laws_count')->default(0);
            $table->unsignedInteger('law_enforcement_study_count')->default(0);
            $table->unsignedInteger('impact_effective_count')->default(0);
            $table->unsignedInteger('impact_ineffective_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'budget_year_ec'], 'legal_studies_org_year_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_studies_disseminations');
    }
};
