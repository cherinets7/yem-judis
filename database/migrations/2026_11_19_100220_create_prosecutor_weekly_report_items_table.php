<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prosecutor_weekly_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prosecutor_weekly_report_id')
                ->constrained('prosecutor_weekly_reports', indexName: 'pwr_items_report_fk')
                ->cascadeOnDelete();
            $table->string('section_code');
            $table->string('row_label');
            $table->json('metrics')->nullable();
            $table->timestamps();

            $table->index(['prosecutor_weekly_report_id', 'section_code'], 'pwr_items_report_section_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prosecutor_weekly_report_items');
    }
};
