<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Civic association register. Code 012.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('civic_associations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->string('association_name');
            $table->text('address')->nullable();
            $table->string('association_type')->nullable();
            $table->unsignedInteger('members_male')->default(0);
            $table->unsignedInteger('members_female')->default(0);
            $table->unsignedInteger('members_total')->default(0);
            $table->decimal('capital_amount', 15, 2)->nullable();
            $table->string('registration_number')->nullable();
            $table->string('certificate_issued_date_ec')->nullable();
            $table->decimal('registration_fee', 12, 2)->nullable();
            $table->decimal('service_fee', 12, 2)->nullable();
            $table->boolean('is_renewed_this_year')->default(false);
            $table->foreignId('served_by_expert')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'registration_number'], 'civic_associations_org_reg_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('civic_associations');
    }
};
