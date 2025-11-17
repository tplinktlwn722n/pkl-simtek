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
        Schema::table('attendances', function (Blueprint $table) {
            // Add GPS coordinates
            $table->decimal('latitude', 10, 8)->nullable()->after('location');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            
            // Remove check-out related columns
            $table->dropColumn(['check_out_time', 'photo_check_out']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Restore check-out columns
            $table->timestamp('check_out_time')->nullable();
            $table->string('photo_check_out')->nullable();
            
            // Remove GPS columns
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
