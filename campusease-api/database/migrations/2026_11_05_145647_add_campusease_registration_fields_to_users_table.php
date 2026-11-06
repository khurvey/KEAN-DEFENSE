
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('department')
                ->nullable();

            $table->unsignedTinyInteger('year_level')
                ->nullable();

            $table->string('block')
                ->nullable();

            $table->string('approval_status')
                ->default('approved');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'department',
                'year_level',
                'block',
                'approval_status',
            ]);
        });
    }
};
