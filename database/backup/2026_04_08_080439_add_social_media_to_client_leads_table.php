<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('client_leads', function (Blueprint $table) {
            // Social Media Links (optional)
            $table->string('facebook')->nullable()->after('website');
            $table->string('instagram')->nullable()->after('facebook');
            $table->string('twitter')->nullable()->after('instagram');
            $table->string('linkedin')->nullable()->after('twitter');
            $table->string('pinterest')->nullable()->after('linkedin');
            $table->string('reddit')->nullable()->after('pinterest');
            $table->string('youtube')->nullable()->after('reddit');
            $table->string('tiktok')->nullable()->after('youtube');
            $table->string('whatsapp')->nullable()->after('tiktok');
            
            // Screenshots (multiple)
            $table->text('screenshots')->nullable()->after('whatsapp'); // JSON array
            
            // Remove budget fields (not needed for hunting)
            $table->dropColumn(['budget_min', 'budget_max']);
        });
    }

    public function down()
    {
        Schema::table('client_leads', function (Blueprint $table) {
            $table->dropColumn([
                'facebook', 'instagram', 'twitter', 'linkedin', 
                'pinterest', 'reddit', 'youtube', 'tiktok', 'whatsapp', 'screenshots'
            ]);
            $table->decimal('budget_min', 10, 2)->nullable();
            $table->decimal('budget_max', 10, 2)->nullable();
        });
    }
};