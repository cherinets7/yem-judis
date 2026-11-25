<?php

namespace App\Models;

use App\Enums\StaffCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfile extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'work_unit_id',
        'staff_category',
        'full_name',
        'birth_year_ec',
        'schools_attended',
        'primary_school_and_year',
        'secondary_school_and_year',
        'college_university',
        'college_grad_year',
        'education_level',
        'profession_track',
        'higher_ed_gpa',
        'position_id_number',
        'job_title',
        'pension_id_number',
        'hire_year_ec',
        'initial_salary',
        'current_salary',
        'salary_history',
        'performance_evaluations',
        'promotions',
        'guarantor_name',
        'years_to_retirement',
        'created_by',
        'filled_by_expert',
    ];

    protected $casts = [
        'staff_category' => StaffCategory::class,
        'birth_year_ec' => 'integer',
        'college_grad_year' => 'integer',
        'hire_year_ec' => 'integer',
        'higher_ed_gpa' => 'decimal:2',
        'initial_salary' => 'decimal:2',
        'current_salary' => 'decimal:2',
        'salary_history' => 'array',
        'performance_evaluations' => 'array',
        'promotions' => 'array',
        'years_to_retirement' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function workUnit(): BelongsTo
    {
        return $this->belongsTo(WorkUnit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function filledByExpert(): BelongsTo
    {
        return $this->belongsTo(User::class, 'filled_by_expert');
    }
}
