<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_number')->nullable()->unique()->after('id');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete()->after('password');
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete()->after('department_id');
            $table->boolean('is_active')->default(true)->after('location_id');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['location_id']);
            $table->dropColumn(['employee_number', 'department_id', 'location_id', 'is_active', 'last_login_at']);
        });
    }
};
