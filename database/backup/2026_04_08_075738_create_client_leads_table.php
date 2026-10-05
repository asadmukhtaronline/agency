<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('client_leads', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            
            // Foreign Keys
            $table->foreignId('client_type_id')->nullable()->constrained('client_types')->onDelete('set null');
            $table->foreignId('work_type_id')->nullable()->constrained('work_types')->onDelete('set null');
            
            // Lead Details
            $table->text('requirements')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('budget_min', 10, 2)->nullable();
            $table->decimal('budget_max', 10, 2)->nullable();
            
            // Status and Priority
            $table->string('status')->default('new'); // new, contacted, meeting, proposal, negotiation, won, lost
            $table->string('priority')->default('medium'); // low, medium, high
            
            // Dates
            $table->date('next_followup')->nullable();
            $table->datetime('contacted_at')->nullable();
            $table->datetime('meeting_at')->nullable();
            
            // Location
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            
            // Additional
            $table->string('source')->nullable(); // linkedin, facebook, referral, etc.
            $table->integer('lead_score')->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_leads');
    }
};