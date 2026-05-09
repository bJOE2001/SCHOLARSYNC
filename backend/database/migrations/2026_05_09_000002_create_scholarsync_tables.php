<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarships', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('scholarship_name');
            $table->string('scholarship_type')->nullable();
            $table->string('academic_year')->nullable();
            $table->string('semester')->nullable();
            $table->decimal('minimum_gpa', 4, 2)->nullable();
            $table->string('year_level_allowed')->nullable();
            $table->string('program_allowed')->nullable();
            $table->unsignedInteger('available_slots')->default(0);
            $table->date('deadline')->nullable();
            $table->date('date_posted')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('status')->default('Draft');
            $table->text('description')->nullable();
            $table->text('eligibility_requirements')->nullable();
            $table->text('required_documents')->nullable();
            $table->text('announcement_details')->nullable();
            $table->timestamps();

            $table->index(['status', 'scholarship_type']);
        });

        Schema::create('scholarship_applications', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('scholarship_id')->nullable();
            $table->string('applicant_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('program');
            $table->string('course')->nullable();
            $table->string('year_level')->nullable();
            $table->decimal('gpa', 4, 2)->nullable();
            $table->text('address')->nullable();
            $table->text('reason');
            $table->date('date_submitted');
            $table->string('status')->default('Pending');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('scholarship_id')->references('id')->on('scholarships')->nullOnDelete();
            $table->index(['status', 'date_submitted']);
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('scholarship_application_id')->nullable();
            $table->string('student_name');
            $table->string('program')->nullable();
            $table->string('document_type');
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->date('upload_date')->nullable();
            $table->string('verification_status')->default('Pending');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('scholarship_application_id')
                ->references('id')
                ->on('scholarship_applications')
                ->cascadeOnDelete();
            $table->index(['verification_status', 'upload_date']);
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('audience');
            $table->date('publish_date');
            $table->text('message');
            $table->timestamps();

            $table->index(['audience', 'publish_date']);
        });

        Schema::create('student_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('compliance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('scholar_name');
            $table->decimal('gpa', 4, 2);
            $table->unsignedTinyInteger('compliance_score');
            $table->string('compliance_status');
            $table->string('risk_level');
            $table->string('gpa_trend')->nullable();
            $table->unsignedTinyInteger('risk_score')->nullable();
            $table->string('forecast_label')->nullable();
            $table->timestamps();

            $table->index(['compliance_status', 'risk_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_records');
        Schema::dropIfExists('student_notifications');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('scholarship_applications');
        Schema::dropIfExists('scholarships');
    }
};
