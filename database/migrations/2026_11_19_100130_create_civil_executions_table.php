<?php

use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Civil execution register. Code 007.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('civil_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->string('execution_plaintiff')->nullable();
            $table->text('plaintiff_address')->nullable();
            $table->string('execution_defendant')->nullable();
            $table->text('defendant_address')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->enum('gender', Gender::values())->nullable();
            $table->string('prosecutor_file_number');
            $table->string('executing_court')->nullable();
            $table->string('court_file_number')->nullable();
            $table->decimal('requested_money_amount', 15, 2)->nullable();
            $table->decimal('requested_land_size', 14, 4)->nullable();
            $table->text('court_decision_date_and_summary')->nullable();
            $table->decimal('recovered_asset_amount', 15, 2)->nullable();
            $table->decimal('unrecovered_asset_amount', 15, 2)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('prosecutor_file_number');
            $table->index('court_file_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('civil_executions');
    }
};
