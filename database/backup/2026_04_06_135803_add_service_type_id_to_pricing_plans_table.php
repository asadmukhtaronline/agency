<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pricing_plans', function (Blueprint $table) {
            // Add service_type_id column after country_id
            $table->foreignId('service_type_id')->nullable()->after('country_id')->constrained('service_types')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('pricing_plans', function (Blueprint $table) {
            $table->dropForeign(['service_type_id']);
            $table->dropColumn('service_type_id');
        });
    }
};