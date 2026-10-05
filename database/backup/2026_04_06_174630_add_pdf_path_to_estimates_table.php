<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('terms_conditions');
        });
    }

    public function down()
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
        });
    }
};