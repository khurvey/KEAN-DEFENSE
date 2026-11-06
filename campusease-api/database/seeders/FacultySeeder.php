<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        $faculty = [
            [
                'name' => 'Sir Kean Ramos',
                'email' => 'kean.demo@example.com',
                'subjects' => [
                    'Web Development',
                    'Advanced Web Development',
                    'Mobile Application Development',
                ],
            ],
            [
                'name' => "Ma'am Judy Ann Flores",
                'email' => 'judy.demo@example.com',
                'subjects' => [
                    'System Administration and Maintenance',
                    'Linux System Administration',
                    'Network Administration',
                ],
            ],
            [
                'name' => 'Sir Mark Tiangson',
                'email' => 'mark.demo@example.com',
                'subjects' => [
                    'Oracle Database',
                    'Database Management Systems',
                    'SQL and PL/SQL Programming',
                ],
            ],
            [
                'name' => 'Sir Arnaldy Fortin',
                'email' => 'arnaldy.demo@example.com',
                'subjects' => [
                    'Information Assurance and Security',
                    'Cybersecurity Fundamentals',
                    'Network Security',
                ],
            ],
        ];

        // Hide old demo faculty without deleting appointment history.
        $oldEmails = [
            'maria.demo@example.com',
            'john.demo@example.com',
            'ana.demo@example.com',
        ];

        foreach ($oldEmails as $email) {
            $old = User::where('email', $email)
                ->where('role', 'faculty')
                ->first();

            if ($old) {
                $old->tokens()->delete();
                $old->role = 'inactive';
                $old->save();
            }
        }

        foreach ($faculty as $item) {
            $user = User::firstOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['name'],
                    'password' => Hash::make('Faculty123!'),
                ]
            );

            $user->name = $item['name'];
            $user->role = 'faculty';
            $user->save();

            foreach ($item['subjects'] as $subject) {
                DB::table('faculty_subjects')->updateOrInsert(
                    [
                        'faculty_id' => $user->id,
                        'subject_name' => $subject,
                    ],
                    [
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
