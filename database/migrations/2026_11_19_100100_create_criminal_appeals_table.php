<?php

use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Criminal appeal register. Code 003.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('criminal_appeals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->string('prosecutor_appeal_number');
            $table->string('lower_court_file_number')->nullable();
            $table->string('appellant_name')->nullable();
            $table->string('respondent_name')->nullable();
            $table->text('address')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->enum('gender', Gender::values())->nullable();
            $table->text('lower_court_decision')->nullable();
            $table->string('appeal_filed_date_ec')->nullable();
            $table->string('appellate_court_and_file_no')->nullable();
            $table->string('assigned_prosecutor')->nullable();
            $table->text('decision_date_and_outcome')->nullable();
            $table->string('case_age')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('prosecutor_appeal_number');
            $table->index('lower_court_file_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('criminal_appeals');
    }
};
