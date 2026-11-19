<?php

use App\Enums\StaffCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('work_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('staff_category', StaffCategory::values());
            $table->string('full_name');
            $table->unsignedSmallInteger('birth_year_ec')->nullable();
            $table->text('schools_attended')->nullable();
            $table->string('primary_school_and_year')->nullable();
            $table->string('secondary_school_and_year')->nullable();
            $table->string('college_university')->nullable();
            $table->unsignedSmallInteger('college_grad_year')->nullable();
            $table->string('education_level')->nullable();
            $table->string('profession_track')->nullable();
            $table->decimal('higher_ed_gpa', 4, 2)->nullable();
            $table->string('position_id_number')->nullable();
            $table->string('job_title')->nullable();
            $table->string('pension_id_number')->nullable();
            $table->unsignedSmallInteger('hire_year_ec')->nullable();
            $table->decimal('initial_salary', 12, 2)->nullable();
            $table->decimal('current_salary', 12, 2)->nullable();
            $table->json('salary_history')->nullable();
            $table->json('performance_evaluations')->nullable();
            $table->json('promotions')->nullable();
            $table->string('guarantor_name')->nullable();
            $table->unsignedTinyInteger('years_to_retirement')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('filled_by_expert')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'staff_category'], 'staff_profiles_org_category_idx');
            $table->index('position_id_number');
            $table->index('pension_id_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
    }
};
