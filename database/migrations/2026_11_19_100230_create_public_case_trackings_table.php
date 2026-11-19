<?php

use App\Enums\CourtLevel;
use App\Enums\FinalOutcome;
use App\Enums\PublicCaseType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_case_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->string('prosecutor_file_number');
            $table->string('court_file_number')->nullable();
            $table->string('plaintiff_name')->nullable();
            $table->string('defendant_name')->nullable();
            $table->enum('case_type', PublicCaseType::values());
            $table->enum('court_level', CourtLevel::values());
            $table->string('appointment_date_ec')->nullable();
            $table->text('appointment_reason')->nullable();
            $table->enum('final_outcome', FinalOutcome::values())->default(FinalOutcome::Pending->value);
            $table->boolean('is_published')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'case_type', 'is_published'], 'public_cases_org_type_pub_idx');
            $table->index('prosecutor_file_number');
            $table->index('court_file_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_case_trackings');
    }
};
