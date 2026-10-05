<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// Migration file mein
public function up()
{
    Schema::table('quote_requests', function (Blueprint $table) {
        $table->text('admin_notes')->nullable()->after('message');
        $table->decimal('quoted_amount', 10, 2)->nullable()->after('admin_notes');
        $table->timestamp('quoted_at')->nullable()->after('quoted_amount');
    });
}

public function down()
{
    Schema::table('quote_requests', function (Blueprint $table) {
        $table->dropColumn(['admin_notes', 'quoted_amount', 'quoted_at']);
    });
}
};
