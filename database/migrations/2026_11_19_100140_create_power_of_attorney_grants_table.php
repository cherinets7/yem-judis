<?php

use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Power of attorney grant register. Code 008.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('power_of_attorney_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->string('grantor_name');
            $table->text('grantor_address')->nullable();
            $table->unsignedTinyInteger('grantor_age')->nullable();
            $table->enum('grantor_gender', Gender::values())->nullable();
            $table->text('poa_content')->nullable();
            $table->string('registration_number');
            $table->string('granted_date_ec')->nullable();
            $table->decimal('stamp_fee', 12, 2)->nullable();
            $table->decimal('service_fee', 12, 2)->nullable();
            $table->string('grantee_name');
            $table->text('grantee_address')->nullable();
            $table->unsignedTinyInteger('grantee_age')->nullable();
            $table->enum('grantee_gender', Gender::values())->nullable();
            $table->foreignId('approved_by_expert')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'registration_number'], 'poa_grants_org_reg_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('power_of_attorney_grants');
    }
};
