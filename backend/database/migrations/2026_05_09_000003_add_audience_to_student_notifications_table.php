<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_notifications', function (Blueprint $table): void {
            if (! Schema::hasColumn('student_notifications', 'audience')) {
                $table->string('audience')->default('student')->after('user_id');
                $table->index(['audience', 'delivered_at']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_notifications', function (Blueprint $table): void {
            if (Schema::hasColumn('student_notifications', 'audience')) {
                $table->dropIndex(['audience', 'delivered_at']);
                $table->dropColumn('audience');
            }
        });
    }
};
