<?php

use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Legal secretary licensing register. Code 014.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_secretaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->string('full_name');
            $table->unsignedTinyInteger('age')->nullable();
            $table->enum('gender', Gender::values())->nullable();
            $table->text('address')->nullable();
            $table->string('license_number')->nullable();
            $table->string('licensed_date_ec')->nullable();
            $table->unsignedInteger('clients_served_male')->default(0);
            $table->unsignedInteger('clients_served_female')->default(0);
            $table->unsignedInteger('clients_served_total')->default(0);
            $table->decimal('service_fee', 12, 2)->nullable();
            $table->decimal('license_fee', 12, 2)->nullable();
            $table->boolean('is_renewed_this_year')->default(false);
            $table->foreignId('served_by_expert')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'license_number'], 'legal_secretaries_org_license_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_secretaries');
    }
};
