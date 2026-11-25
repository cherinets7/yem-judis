<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('civil_appeals', function (Blueprint $table): void {
            $table->string('appellant_name')->nullable()->after('appellant_type');
            $table->string('respondent_name')->nullable()->after('appellant_name');
        });

        Schema::table('laws', function (Blueprint $table): void {
            $table->boolean('is_published')->default(false)->after('file_path');
        });

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::table('laws')->update(['is_published' => true]);
        } else {
            DB::table('laws')->update(['is_published' => 1]);
        }

        Schema::table('settings', function (Blueprint $table): void {
            $table->text('about_text')->nullable()->after('footer_note');
            $table->text('services_text')->nullable()->after('about_text');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn(['about_text', 'services_text']);
        });

        Schema::table('laws', function (Blueprint $table): void {
            $table->dropColumn('is_published');
        });

        Schema::table('civil_appeals', function (Blueprint $table): void {
            $table->dropColumn(['appellant_name', 'respondent_name']);
        });
    }
};
