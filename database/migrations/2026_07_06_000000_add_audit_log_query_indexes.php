<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table): void {
            $table->index('event');
            $table->index('created_at');
            $table->index(['causer_type', 'causer_id']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table): void {
            $table->dropIndex(['event']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['causer_type', 'causer_id']);
            $table->dropIndex(['subject_type', 'subject_id']);
        });
    }
};
