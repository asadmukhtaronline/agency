<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('service_type')->nullable();
            $table->text('message');
            $table->json('requirements')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'quoted', 'accepted', 'rejected'])->default('pending');
            $table->decimal('quoted_amount', 10, 2)->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('quoted_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quotations');
    }
};