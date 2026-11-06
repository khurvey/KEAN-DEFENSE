<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $registrar = DB::table('offices')->insertGetId([
            'name' => "Registrar's Office",
            'location' => 'Main Campus',
            'description' => 'Academic records and student documents',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $guidance = DB::table('offices')->insertGetId([
            'name' => 'Guidance Office',
            'location' => 'Main Campus',
            'description' => 'Student counseling and consultation',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $accounting = DB::table('offices')->insertGetId([
            'name' => 'Accounting Office',
            'location' => 'Main Campus',
            'description' => 'Student payments and account concerns',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('services')->insert([
            [
                'office_id' => $registrar,
                'name' => 'Document Request',
                'description' => 'Request academic documents',
                'type' => 'request',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'office_id' => $registrar,
                'name' => 'Registrar Consultation',
                'description' => 'Schedule a consultation',
                'type' => 'appointment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'office_id' => $guidance,
                'name' => 'Guidance Counseling',
                'description' => 'Book a counseling appointment',
                'type' => 'appointment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'office_id' => $accounting,
                'name' => 'Payment Consultation',
                'description' => 'Discuss payment concerns',
                'type' => 'appointment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}