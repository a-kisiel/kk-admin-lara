<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Piece;
use App\Models\Collection;
use App\Models\Medium;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('location')->nullable();
            $table->mediumText('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('pieces', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->mediumText('description')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('width')->unsigned()->nullable();
            $table->integer('height')->unsigned()->nullable();
            $table->string('actual_size')->nullable();
            $table->string('hash')->nullable();
            $table->boolean('is_wallpaper')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('pieces');
        });

        Schema::create('collection_pieces', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Collection::class);
            $table->foreignIdFor(Piece::class);
        });
        Schema::create('piece_media', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Piece::class);
            $table->foreignIdFor(Medium::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('pieces');
        Schema::dropIfExists('collection_pieces');
        Schema::dropIfExists('piece_media');
        Schema::dropIfExists('piece_children');
    }
};
