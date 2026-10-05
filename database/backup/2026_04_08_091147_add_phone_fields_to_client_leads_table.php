<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('client_leads', function (Blueprint $table) {
            $table->string('alternative_phone')->nullable()->after('phone');
            $table->boolean('has_whatsapp')->default(false)->after('alternative_phone');
            $table->string('whatsapp_number')->nullable()->after('has_whatsapp');
            $table->foreignId('country_id')->nullable()->after('whatsapp_number')->constrained('countries')->onDelete('set null');
            $table->string('country_code')->nullable()->after('country_id');
        });
    }

    public function down()
    {
        Schema::table('client_leads', function (Blueprint $table) {
            $table->dropColumn(['alternative_phone', 'has_whatsapp', 'whatsapp_number', 'country_id', 'country_code']);
        });
    }
};