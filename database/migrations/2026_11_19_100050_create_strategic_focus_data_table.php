<?php

use App\Enums\Quarter;
use App\Enums\ReviewStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strategic_focus_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('work_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('budget_year_ec');
            $table->enum('quarter', Quarter::values());
            $table->string('focus_area');
            $table->decimal('target_value', 15, 2)->nullable();
            $table->decimal('achieved_value', 15, 2)->nullable();
            $table->text('narrative_summary')->nullable();
            $table->enum('status', ReviewStatus::values())->default(ReviewStatus::Draft->value);
            $table->timestamps();

            $table->index(['organization_id', 'budget_year_ec', 'quarter'], 'strategic_focus_org_year_q_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strategic_focus_data');
    }
};
