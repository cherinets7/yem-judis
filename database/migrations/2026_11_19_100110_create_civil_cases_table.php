<?php

use App\Enums\CourtLevel;
use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Civil case register.
 * Code 004 high court. Code 005 woreda court.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('civil_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->enum('court_level', CourtLevel::values());
            $table->string('plaintiff_name');
            $table->string('defendant_name')->nullable();
            $table->text('plaintiff_address')->nullable();
            $table->text('defendant_address')->nullable();
            $table->enum('gender', Gender::values())->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('prosecutor_case_number')->nullable();
            $table->text('negotiated_recovered_asset')->nullable();
            $table->string('filed_date_ec')->nullable();
            $table->decimal('claim_money_amount', 15, 2)->nullable();
            $table->decimal('claim_land_size', 14, 4)->nullable();
            $table->string('assigned_prosecutor')->nullable();
            $table->string('court_file_number')->nullable();
            $table->text('decision_date_and_type')->nullable();
            $table->string('case_age')->nullable();
            $table->unsignedInteger('witness_male')->default(0);
            $table->unsignedInteger('witness_female')->default(0);
            $table->unsignedInteger('witness_total')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'court_level'], 'civil_cases_org_court_idx');
            $table->index('prosecutor_case_number');
            $table->index('court_file_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('civil_cases');
    }
};
