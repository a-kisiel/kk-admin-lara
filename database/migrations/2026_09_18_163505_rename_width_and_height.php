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
            $table->renameColumn('width', 'image_width');
            $table->renameColumn('height', 'image_height');
            $table->string('image_color')->after('image_height')->nullable();

            $table->dropColumn('actual_size');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->integer('image_width')->after('hash')->nullable();
            $table->integer('image_height')->after('image_width')->nullable();
            $table->string('image_color')->after('image_height')->nullable();
        });

        Schema::table('sketches', function (Blueprint $table) {
            $table->integer('image_width')->after('hash')->nullable();
            $table->integer('image_height')->after('image_width')->nullable();
            $table->string('image_color')->after('image_height')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pieces', function (Blueprint $table) {
            $table->renameColumn('image_width', 'width');
            $table->renameColumn('image_height', 'height');
            $table->dropColumn('image_color');

            $table->string('actual_size')->nullable();
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('image_width');
            $table->dropColumn('image_height');
            $table->dropColumn('image_color');
        });

        Schema::table('sketches', function (Blueprint $table) {
            $table->dropColumn('image_width');
            $table->dropColumn('image_height');
            $table->dropColumn('image_color');
        });
    }
};
