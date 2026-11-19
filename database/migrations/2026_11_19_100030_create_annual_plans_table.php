<?php

use App\Enums\PlanDocumentType;
use App\Enums\ReviewStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annual_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('budget_year_ec');
            $table->string('title');
            $table->enum('document_type', PlanDocumentType::values());
            $table->string('file_path')->nullable();
            $table->text('summary')->nullable();
            $table->enum('status', ReviewStatus::values())->default(ReviewStatus::Draft->value);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'budget_year_ec'], 'annual_plans_org_year_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annual_plans');
    }
};
