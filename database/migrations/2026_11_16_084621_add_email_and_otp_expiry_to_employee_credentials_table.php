<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_credentials', function (Blueprint $table) {
            $table->string('email')->unique()->nullable()->after('nama');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
        });
    }

    public function down(): void
    {
        Schema::table('employee_credentials', function (Blueprint $table) {
            $table->dropColumn(['email', 'otp_expires_at']);
        });
    }
};