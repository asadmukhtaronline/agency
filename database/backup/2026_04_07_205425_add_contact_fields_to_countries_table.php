<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->after('flag');
            $table->string('whatsapp_number')->nullable()->after('phone_number');
            $table->string('email')->nullable()->after('whatsapp_number');
        });
    }

    public function down()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'whatsapp_number', 'email']);
        });
    }
};