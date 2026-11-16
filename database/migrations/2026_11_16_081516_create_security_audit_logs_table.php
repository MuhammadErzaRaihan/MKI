<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source', 32)->default('GATEWAY_LOGIN')->index();
            $table->string('nip', 64)->nullable();
            $table->string('ip_address', 45);
            $table->string('destination_ip', 45)->nullable();
            $table->string('protocol', 20)->default('HTTP');
            $table->string('event_type', 40);
            $table->enum('severity', ['INFO', 'LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])->default('INFO');
            $table->enum('status', ['ALLOWED', 'SUSPICIOUS', 'BLOCKED'])->default('ALLOWED');
            $table->decimal('detection_latency_ms', 8, 3)->default(0);
            $table->text('payload_snippet')->nullable();
            $table->string('recommended_action');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_audit_logs');
    }
};