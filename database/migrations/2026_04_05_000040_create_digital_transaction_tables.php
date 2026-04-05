<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('digital_service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manual_channel_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->string('status')->index();
            $table->string('destination_account');
            $table->string('destination_name')->nullable();
            $table->bigInteger('nominal_amount');
            $table->bigInteger('fee_amount')->default(0);
            $table->bigInteger('total_amount');
            $table->bigInteger('cash_effect_amount')->default(0);
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('validated_at')->nullable()->index();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('supervisor_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('operator_note')->nullable();
            $table->text('validation_note')->nullable();
            $table->string('external_reference')->nullable();
            $table->boolean('requires_supervisor_approval')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('digital_transaction_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('digital_transaction_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status')->index();
            $table->foreignId('acted_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('acted_at')->index();
            $table->text('note')->nullable();
            $table->string('external_reference')->nullable();
            $table->json('metadata')->nullable();
        });

        Schema::create('manual_validation_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('digital_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->string('note_type')->index();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('transaction_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('digital_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('disk');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('attachment_type')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_attachments');
        Schema::dropIfExists('manual_validation_notes');
        Schema::dropIfExists('digital_transaction_status_logs');
        Schema::dropIfExists('digital_transactions');
    }
};
