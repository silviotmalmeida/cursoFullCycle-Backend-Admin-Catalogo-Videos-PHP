<?php

use App\Models\MediaVideo;
use Core\Domain\Enum\MediaStatus;
use Core\Domain\Enum\MediaType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_medias', function (Blueprint $table) {
            $table->uuid('id')->primary()->autoIncrement();
            $table->uuid('video_id')->index();
            $table->foreign('video_id')->references('id')->on('videos')->onDelete('cascade');
            $table->string('file_path');
            $table->string('encoded_path')->nullable();
            $table->enum('status', array_keys(MediaStatus::cases()));
            $table->enum('type', array_keys(MediaType::cases()));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_medias');
    }
};
