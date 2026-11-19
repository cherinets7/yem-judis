<?php

use App\Enums\CourtLevel;
use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Criminal case register.
 * Code 001 woreda court. Code 002 high court.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('criminal_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->enum('court_level', CourtLevel::values());
            $table->string('prosecutor_file_number');
            $table->string('police_file_number_and_pages')->nullable();
            $table->string('complainant_and_police_date')->nullable();
            $table->string('submitted_to_justice_date')->nullable();
            $table->text('defendant_names')->nullable();
            $table->text('defendant_address')->nullable();
            $table->enum('defendant_gender', Gender::values())->nullable();
            $table->unsignedTinyInteger('defendant_age')->nullable();
            $table->text('police_investigated_crime')->nullable();
            $table->string('victim_and_age')->nullable();
            $table->text('prosecutor_charged_crime')->nullable();
            $table->string('filed_in_court_date')->nullable();
            $table->string('court_file_number')->nullable();
            $table->string('court_decision_type')->nullable();
            $table->boolean('closed_indefinitely')->default(false);
            $table->string('case_age')->nullable();
            $table->string('assigned_prosecutor')->nullable();
            $table->string('witnesses_count_and_gender')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'court_level'], 'criminal_cases_org_court_idx');
            $table->index('prosecutor_file_number');
            $table->index('court_file_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('criminal_cases');
    }
};
