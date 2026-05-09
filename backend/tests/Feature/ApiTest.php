<?php

namespace Tests\Feature;

use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_endpoint_returns_a_healthy_response(): void
    {
        $response = $this->getJson('/api/test');

        $response
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'message' => 'ScholarSync API is running.',
            ]);
    }

    public function test_student_can_register_and_login(): void
    {
        $this->postJson('/api/auth/register', [
            'firstName' => 'Nina',
            'lastName' => 'Dela Cruz',
            'email' => 'nina.delacruz@college.edu',
            'phone' => '0917 111 2222',
            'program' => 'BS Information Technology',
            'yearLevel' => '1st Year',
            'password' => 'password123',
            'confirmPassword' => 'password123',
        ])
            ->assertCreated()
            ->assertJsonPath('message', 'Registration successful.')
            ->assertJsonPath('user.email', 'nina.delacruz@college.edu')
            ->assertJsonStructure(['token']);

        $this->postJson('/api/auth/login', [
            'email' => 'nina.delacruz@college.edu',
            'password' => 'password123',
            'role' => 'student',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Login successful.')
            ->assertJsonPath('user.role', 'student')
            ->assertJsonStructure(['token']);
    }

    public function test_student_can_submit_application_and_admin_can_update_status(): void
    {
        $student = User::create([
            'name' => 'Alyssa Mendoza',
            'email' => 'alyssa.test@college.edu',
            'password' => 'password123',
            'role' => 'student',
            'phone' => '0917 245 8831',
            'program' => 'BS Information Technology',
            'year_level' => '3rd Year',
            'address' => 'San Isidro, Cabanatuan City, Nueva Ecija',
        ]);

        Scholarship::create([
            'id' => 'sch-test',
            'scholarship_name' => 'Academic Excellence Grant',
            'available_slots' => 25,
            'status' => 'Open',
        ]);

        $applicationId = $this->postJson('/api/applications', [
            'userId' => $student->id,
            'scholarshipProgram' => 'Academic Excellence Grant',
            'gpa' => '1.45',
            'yearLevel' => '3rd Year',
            'address' => 'San Isidro, Cabanatuan City, Nueva Ecija',
            'reason' => 'I want to continue my studies with financial support.',
        ])
            ->assertCreated()
            ->assertJsonPath('message', 'Application submitted.')
            ->assertJsonPath('data.applicantName', 'Alyssa Mendoza')
            ->assertJsonPath('data.program', 'Academic Excellence Grant')
            ->json('data.id');

        $this->patchJson("/api/applications/{$applicationId}/status", [
            'status' => 'Under Review',
            'remarks' => 'Application is complete and ready for review.',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'Under Review')
            ->assertJsonPath('data.remarks', 'Application is complete and ready for review.');

        $this->assertDatabaseHas('student_notifications', [
            'audience' => 'admin',
            'title' => 'New scholarship application',
            'message' => 'Alyssa Mendoza applied for Academic Excellence Grant.',
        ]);
        $this->assertDatabaseHas('student_notifications', [
            'user_id' => $student->id,
            'audience' => 'student',
            'title' => 'Application submitted',
        ]);
        $this->assertDatabaseHas('student_notifications', [
            'user_id' => $student->id,
            'audience' => 'student',
            'title' => 'Application under review',
            'message' => 'Your application for Academic Excellence Grant is now under review.',
        ]);

        $this->getJson('/api/notifications?audience=admin')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'New scholarship application');

        $this->patchJson("/api/applications/{$applicationId}/status", [
            'status' => 'Approved',
            'remarks' => 'Approved for the current scholarship cycle.',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'Approved');

        $this->assertDatabaseHas('compliance_records', [
            'user_id' => $student->id,
            'scholar_name' => 'Alyssa Mendoza',
            'compliance_status' => 'Compliant',
            'risk_level' => 'Low',
            'forecast_label' => 'Low Risk',
        ]);
        $this->assertDatabaseHas('student_notifications', [
            'user_id' => $student->id,
            'audience' => 'student',
            'title' => 'Application approved',
            'message' => 'Your application for Academic Excellence Grant has been approved.',
        ]);

        $this->patchJson("/api/applications/{$applicationId}/status", [
            'status' => 'Approved',
            'remarks' => 'Approved for the current scholarship cycle.',
        ])->assertOk();

        $this->assertDatabaseCount('compliance_records', 1);
    }

    public function test_seeder_creates_user_accounts_and_one_scholarship(): void
    {
        $today = now()->toDateString();
        $deadline = now()->addDays(15)->toDateString();

        $this->seed();

        $this->assertDatabaseCount('users', 9);
        $this->assertDatabaseHas('users', [
            'email' => 'camille.navarro@scholarsync.edu',
            'role' => 'administrator',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'alyssa.mendoza@college.edu',
            'role' => 'student',
        ]);
        $this->assertDatabaseCount('scholarships', 1);
        $this->assertDatabaseHas('scholarships', [
            'id' => 'sch-001',
            'scholarship_name' => 'Academic Excellence Grant',
            'status' => 'Open',
        ]);
        $scholarship = Scholarship::findOrFail('sch-001');
        $this->assertSame($today, $scholarship->date_posted->toDateString());
        $this->assertSame($deadline, $scholarship->deadline->toDateString());
        $this->assertDatabaseCount('scholarship_applications', 0);
        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('student_notifications', 0);

        $this->getJson('/api/dashboard/admin')
            ->assertOk()
            ->assertJsonPath('stats.0.title', 'Total Applicants')
            ->assertJsonPath('stats.0.value', '0')
            ->assertJsonPath('stats.1.title', 'Pending Applications')
            ->assertJsonPath('stats.1.value', '0');

        $this->getJson('/api/scholarships')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.scholarshipName', 'Academic Excellence Grant')
            ->assertJsonPath('data.0.datePostedIso', $today)
            ->assertJsonPath('data.0.deadlineIso', $deadline);

        $this->getJson('/api/documents')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
