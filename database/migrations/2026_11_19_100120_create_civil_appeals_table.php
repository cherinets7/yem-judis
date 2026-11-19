<?php

use App\Enums\AppellantType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Civil appeal register. Code 006.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('civil_appeals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->enum('appellant_type', AppellantType::values());
            $table->text('appellant_address')->nullable();
            $table->text('respondent_address')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('prosecutor_appeal_number');
            $table->string('appeal_filed_date_ec')->nullable();
            $table->string('lower_court_file_number')->nullable();
            $table->string('appealed_court_name')->nullable();
            $table->string('appellate_court_file_number')->nullable();
            $table->decimal('appeal_money_amount', 15, 2)->nullable();
            $table->decimal('appeal_land_size', 14, 4)->nullable();
            $table->text('appellate_decision_and_date')->nullable();
            $table->string('case_age')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('prosecutor_appeal_number');
            $table->index('lower_court_file_number');
            $table->index('appellate_court_file_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('civil_appeals');
    }
};
