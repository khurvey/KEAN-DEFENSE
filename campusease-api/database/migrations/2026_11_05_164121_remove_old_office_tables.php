
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove old office system tables.
        // Child tables must be deleted first.

        Schema::dropIfExists('service_requests');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('available_slots');
        Schema::dropIfExists('services');
        Schema::dropIfExists('offices');
    }

    public function down(): void
    {
        // Old tables will not be recreated.
        // Use the SQL backup if restoration is needed.
    }
};
