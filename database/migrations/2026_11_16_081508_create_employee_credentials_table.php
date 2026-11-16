<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 18)->unique();
            $table->string('nama');
            // Nullable so records created from the ID Card modal without a password still insert cleanly.
            $table->string('password')->nullable();
            $table->string('instansi');
            $table->string('ttl')->nullable();
            $table->string('telp', 25)->nullable();
            $table->enum('status', ['ON', 'OFF'])->default('ON');
            $table->string('ip_detected', 64)->nullable();
            $table->boolean('is_office_ip')->default(true);
            $table->string('secret_key', 32)->unique();
            $table->string('otp_code', 6)->default('123456');
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_credentials');
    }
};