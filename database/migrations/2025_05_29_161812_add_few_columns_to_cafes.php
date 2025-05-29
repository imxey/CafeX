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
        Schema::table('cafes', function (Blueprint $table) {
            $table->text('address')->nullable()->after('maps');
            $table->string('image_url')->nullable()->after('address');
            $table->time('open_time')->nullable()->after('image_url');
            $table->time('close_time')->nullable()->after('open_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cafes', function (Blueprint $table) {
            //
        });
    }
};
