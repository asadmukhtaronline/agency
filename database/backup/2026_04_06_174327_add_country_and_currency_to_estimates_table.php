<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
            $table->string('currency_symbol')->default('$')->after('country_id');
            $table->foreignId('service_type_id')->nullable()->after('currency_symbol')->constrained()->nullOnDelete();
            $table->foreignId('pricing_plan_id')->nullable()->after('service_type_id')->constrained()->nullOnDelete();
            $table->boolean('is_paid')->default(false)->after('status');
            $table->timestamp('paid_at')->nullable()->after('is_paid');
        });
    }

    public function down()
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['service_type_id']);
            $table->dropForeign(['pricing_plan_id']);
            $table->dropColumn(['country_id', 'currency_symbol', 'service_type_id', 'pricing_plan_id', 'is_paid', 'paid_at']);
        });
    }
};