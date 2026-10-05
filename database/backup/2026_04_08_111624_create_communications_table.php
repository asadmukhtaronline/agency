<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_lead_id')->constrained('client_leads')->onDelete('cascade');
            $table->string('type'); // call, email, meeting, whatsapp
            $table->string('direction')->default('outgoing'); // outgoing, incoming
            $table->string('subject')->nullable();
            $table->text('notes')->nullable();
            $table->string('client_response')->nullable(); // interested, not_interested, call_back, no_response, follow_up
            $table->integer('duration')->nullable(); // minutes for calls
            $table->date('next_followup')->nullable();
            $table->boolean('is_completed')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('communications');
    }
};