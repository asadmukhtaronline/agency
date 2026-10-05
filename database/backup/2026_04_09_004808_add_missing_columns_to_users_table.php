<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Role and status
            $table->string('role')->default('admin')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
            
            // Contact info
            $table->string('phone')->nullable()->after('is_active');
            $table->text('bio')->nullable()->after('phone');
            
            // Avatar
            $table->string('avatar')->nullable()->after('bio');
            
            // Login tracking
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'is_active', 'phone', 'bio', 
                'avatar', 'last_login_at', 'last_login_ip'
            ]);
        });
    }
};