<?php

use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Power of attorney revocation register. Code 009.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('power_of_attorney_revocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->string('revoker_name');
            $table->text('revoker_address')->nullable();
            $table->unsignedTinyInteger('revoker_age')->nullable();
            $table->enum('revoker_gender', Gender::values())->nullable();
            $table->string('previous_poa_ref_and_date')->nullable();
            $table->string('revocation_reg_number');
            $table->string('revoked_date_ec')->nullable();
            $table->decimal('stamp_fee', 12, 2)->nullable();
            $table->decimal('service_fee', 12, 2)->nullable();
            $table->string('revoked_from_name')->nullable();
            $table->text('revoked_from_address')->nullable();
            $table->unsignedTinyInteger('revoked_from_age')->nullable();
            $table->enum('revoked_from_gender', Gender::values())->nullable();
            $table->foreignId('approved_by_expert')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'revocation_reg_number'], 'poa_revocations_org_reg_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('power_of_attorney_revocations');
    }
};
