<?php

use App\Enums\Gender;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attorney licensing register. Code 013.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attorneys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->string('full_name');
            $table->enum('gender', Gender::values())->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->text('address')->nullable();
            $table->string('licensing_body')->nullable();
            $table->string('license_number')->nullable();
            $table->string('licensed_date_ec')->nullable();
            $table->decimal('license_fee', 12, 2)->nullable();
            $table->decimal('service_fee', 12, 2)->nullable();
            $table->unsignedInteger('pro_bono_clients_male')->default(0);
            $table->unsignedInteger('pro_bono_clients_female')->default(0);
            $table->unsignedInteger('pro_bono_clients_total')->default(0);
            $table->unsignedInteger('pro_bono_hours')->default(0);
            $table->unsignedSmallInteger('service_year_ec')->nullable();
            $table->boolean('is_renewed_this_year')->default(false);
            $table->foreignId('served_by_expert')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_id', 'license_number'], 'attorneys_org_license_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attorneys');
    }
};
