<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Book;
use App\Models\Sketch;
use App\Models\Medium;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('dimensions')->nullable();
            $table->mediumText('description')->nullable();
            $table->boolean('active')->default(false);
            $table->string('location')->nullable();
            $table->string('hash')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('books');
        });

        Schema::create('sketches', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('dimensions')->nullable();
            $table->mediumText('description')->nullable();
            $table->boolean('active')->default(false);
            $table->string('location')->nullable();
            $table->string('hash')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('sketches');
        });

        Schema::create('book_media', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Book::class);
            $table->foreignIdFor(Medium::class);
        });

        Schema::create('sketch_media', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Sketch::class);
            $table->foreignIdFor(Medium::class);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->boolean('type')->default(false);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('is_support');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
        Schema::dropIfExists('sketches');
        Schema::dropIfExists('book_media');
        Schema::dropIfExists('sketch_media');

        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->boolean('is_support')->default(false);
        });
    }
};
