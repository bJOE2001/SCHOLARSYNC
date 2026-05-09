<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('student')->after('password');
            }

            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('role');
            }

            if (! Schema::hasColumn('users', 'program')) {
                $table->string('program')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('users', 'year_level')) {
                $table->string('year_level')->nullable()->after('program');
            }

            if (! Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('year_level');
            }

            if (! Schema::hasColumn('users', 'api_token')) {
                $table->string('api_token', 80)->nullable()->unique()->after('remember_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['role', 'phone', 'program', 'year_level', 'address', 'api_token'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
