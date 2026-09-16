<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pieces', function (Blueprint $table) {
            $table->string('dimensions')->nullable();
            $table->string('location')->nullable();
            $table->string('support_id')->nullable();
        });

        Schema::table('media', function (Blueprint $table) {
            $table->boolean('is_support')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pieces', function (Blueprint $table) {
            $table->dropColumn('dimensions');
            $table->dropColumn('location');
            $table->dropColumn('support_id');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('is_support');
        });
    }
};
