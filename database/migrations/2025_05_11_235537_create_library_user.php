<?php

use App\Enums\LibraryRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('library_user', function (Blueprint $table) {
            $table->foreignId('library_id')->constrained('libraries')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('role');
            $table->timestamps();
            $table->unique(['library_id','user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_user');
    }
};
