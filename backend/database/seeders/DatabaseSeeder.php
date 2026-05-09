<?php

namespace Database\Seeders;

use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's base accounts and starter scholarship.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'camille.navarro@scholarsync.edu'],
            [
                'name' => 'Dr. Camille Navarro',
                'password' => Hash::make('password'),
                'role' => 'administrator',
                'phone' => '0917 100 2000',
                'program' => 'Scholarship Office',
                'year_level' => null,
                'address' => null,
            ]
        );

        collect([
            [
                'name' => 'Alyssa Mendoza',
                'email' => 'alyssa.mendoza@college.edu',
                'phone' => '0917 245 8831',
                'program' => 'Bachelor of Science in Information Technology',
                'year_level' => '3rd Year',
                'address' => 'San Isidro, Cabanatuan City, Nueva Ecija',
            ],
            [
                'name' => 'Marcus Reyes',
                'email' => 'marcus.reyes@college.edu',
                'phone' => '0918 552 1940',
                'program' => 'BS Computer Science',
                'year_level' => '2nd Year',
                'address' => 'Bantug, Science City of Munoz, Nueva Ecija',
            ],
            [
                'name' => 'Janelle Cruz',
                'email' => 'janelle.cruz@college.edu',
                'phone' => '0916 733 6182',
                'program' => 'BS Information Systems',
                'year_level' => '4th Year',
                'address' => 'Talavera, Nueva Ecija',
            ],
            [
                'name' => 'Rafael Santos',
                'email' => 'rafael.santos@college.edu',
                'phone' => '0915 902 3371',
                'program' => 'BS Data Science',
                'year_level' => '1st Year',
                'address' => 'Palayan City, Nueva Ecija',
            ],
            [
                'name' => 'Bianca Flores',
                'email' => 'bianca.flores@college.edu',
                'phone' => '0917 481 2047',
                'program' => 'BS Information Technology',
                'year_level' => '3rd Year',
                'address' => 'Gapan City, Nueva Ecija',
            ],
            [
                'name' => 'Noel Garcia',
                'email' => 'noel.garcia@college.edu',
                'phone' => '0917 932 4120',
                'program' => 'BS Information Technology',
                'year_level' => '2nd Year',
                'address' => 'Cabanatuan City, Nueva Ecija',
            ],
            [
                'name' => 'Rina Bautista',
                'email' => 'rina.bautista@college.edu',
                'phone' => '0918 204 9933',
                'program' => 'BS Computer Science',
                'year_level' => '4th Year',
                'address' => 'San Jose City, Nueva Ecija',
            ],
            [
                'name' => 'Jules Aquino',
                'email' => 'jules.aquino@college.edu',
                'phone' => '0917 556 9012',
                'program' => 'BS Data Science',
                'year_level' => '3rd Year',
                'address' => 'Talavera, Nueva Ecija',
            ],
        ])->each(function (array $student): void {
            User::updateOrCreate(
                ['email' => $student['email']],
                [
                    ...$student,
                    'password' => Hash::make('password'),
                    'role' => 'student',
                ]
            );
        });

        $today = now()->toDateString();

        Scholarship::updateOrCreate(
            ['id' => 'sch-001'],
            [
                'scholarship_name' => 'Academic Excellence Grant',
                'description' => 'Merit-based scholarship for students with strong academic performance.',
                'eligibility_requirements' => 'Minimum GPA of 1.75, good moral standing, and active enrollment.',
                'required_documents' => 'Report Card, Certificate of Enrollment, Valid ID, Application Form',
                'deadline' => now()->addDays(15)->toDateString(),
                'available_slots' => 100,
                'announcement_details' => 'Applications are open for qualified students with excellent academic standing.',
                'status' => 'Open',
                'scholarship_type' => 'Merit-Based',
                'academic_year' => '2026-2027',
                'semester' => '1st Semester',
                'minimum_gpa' => 1.75,
                'year_level_allowed' => 'All Year Levels',
                'program_allowed' => 'All Programs',
                'contact_person' => 'Scholarship Office',
                'date_posted' => $today,
            ]
        );
    }
}
