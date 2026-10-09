<?php

namespace Tests\Feature;

use App\Models\MedicalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MedicalCaseManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Requirement 1: Valid login succeeds.
     */
    public function test_valid_login_succeeds(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@jajalmedical.com',
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@jajalmedical.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Requirement 2: Invalid login is rejected.
     */
    public function test_invalid_login_is_rejected(): void
    {
        User::factory()->create([
            'email' => 'viewer@jajalmedical.com',
            'password' => bcrypt('Password123!'),
            'role' => 'viewer',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'viewer@jajalmedical.com',
            'password' => 'WrongPassword!',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Requirement 3: A guest cannot open case pages or APIs (401 / redirect).
     */
    public function test_guest_cannot_open_case_pages_or_apis(): void
    {
        // Web routes should redirect unauthenticated guests to login (302)
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/cases')->assertRedirect(route('login'));
        $this->get('/cases/create')->assertRedirect(route('login'));
        $this->post('/cases', [])->assertRedirect(route('login'));

        // API routes should return 401 Unauthorized for unauthenticated requests
        $this->getJson('/api/cases')->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated. Valid Bearer token required.',
            ]);

        $this->postJson('/api/cases', [])->assertStatus(401);
        $this->deleteJson('/api/cases/1')->assertStatus(401);
    }

    /**
     * Requirement 4: Admin can create a case.
     */
    public function test_admin_can_create_a_case(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Web creation
        $webCaseData = [
            'case_number' => 'CASE-1001',
            'patient_reference' => 'PT-5544-AA',
            'surgeon_name' => 'Dr. Elizabeth Hayes',
            'implant_type' => 'Cranial Titanium Mesh 3D',
            'status' => 'Planning',
            'priority' => 'High',
            'surgery_date' => now()->addWeek()->format('Y-m-d'),
        ];

        $response = $this->actingAs($admin)->post('/cases', $webCaseData);

        $createdCase = MedicalCase::where('case_number', 'CASE-1001')->first();
        $this->assertNotNull($createdCase);
        $response->assertRedirect(route('cases.show', $createdCase));
        $this->assertDatabaseHas('cases', [
            'case_number' => 'CASE-1001',
            'surgeon_name' => 'Dr. Elizabeth Hayes',
            'created_by' => $admin->id,
        ]);

        // API creation with Sanctum
        Sanctum::actingAs($admin);

        $apiCaseData = [
            'case_number' => 'CASE-1002',
            'patient_reference' => 'PT-6677-BB',
            'surgeon_name' => 'Dr. Marcus Vance',
            'implant_type' => 'Acetabular Hip Cup System',
            'status' => 'Approved',
            'priority' => 'Medium',
            'surgery_date' => now()->addDays(10)->format('Y-m-d'),
        ];

        $apiResponse = $this->postJson('/api/cases', $apiCaseData);

        $apiResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Medical case created successfully.',
            ])
            ->assertJsonPath('data.case_number', 'CASE-1002');

        $this->assertDatabaseHas('cases', [
            'case_number' => 'CASE-1002',
            'created_by' => $admin->id,
        ]);
    }

    /**
     * Requirement 5: Creating a case with a duplicate Case Number fails (422).
     */
    public function test_creating_a_case_with_duplicate_case_number_fails(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        MedicalCase::factory()->create([
            'case_number' => 'CASE-DUP-001',
            'created_by' => $admin->id,
        ]);

        // Web submission with duplicate case number fails validation
        $response = $this->actingAs($admin)->from('/cases/create')->post('/cases', [
            'case_number' => 'CASE-DUP-001',
            'patient_reference' => 'PT-9999-ZZ',
            'surgeon_name' => 'Dr. Sarah Jenkins',
            'implant_type' => 'Spinal Fixation Rod',
            'status' => 'Draft',
            'priority' => 'Low',
        ]);

        $response->assertRedirect('/cases/create');
        $response->assertSessionHasErrors('case_number');

        // API submission with duplicate case number returns HTTP 422 with field-level error
        Sanctum::actingAs($admin);

        $apiResponse = $this->postJson('/api/cases', [
            'case_number' => 'CASE-DUP-001',
            'patient_reference' => 'PT-9999-ZZ',
            'surgeon_name' => 'Dr. Sarah Jenkins',
            'implant_type' => 'Spinal Fixation Rod',
            'status' => 'Draft',
            'priority' => 'Low',
        ]);

        $apiResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation error.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'errors' => ['case_number'],
                ],
            ]);
    }

    /**
     * Requirement 6: Viewer cannot delete a case (403).
     */
    public function test_viewer_cannot_delete_a_case(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $viewer = User::factory()->create(['role' => 'viewer']);

        $case = MedicalCase::factory()->create([
            'case_number' => 'CASE-SECURE-01',
            'created_by' => $admin->id,
        ]);

        // Web attempt by Viewer to delete is blocked by server (403 Forbidden)
        $response = $this->actingAs($viewer)->delete("/cases/{$case->id}");
        $response->assertStatus(403);

        // API attempt by Viewer to delete is blocked (403 Forbidden)
        Sanctum::actingAs($viewer);
        $apiResponse = $this->deleteJson("/api/cases/{$case->id}");
        $apiResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);

        // Assert record is NOT deleted from database
        $this->assertDatabaseHas('cases', [
            'id' => $case->id,
            'case_number' => 'CASE-SECURE-01',
        ]);
    }

    /**
     * Bonus Test: Engineer can create and update a case but cannot delete a case (403).
     */
    public function test_engineer_can_update_but_cannot_delete(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $engineer = User::factory()->create(['role' => 'engineer']);

        $case = MedicalCase::factory()->create([
            'case_number' => 'CASE-ENG-001',
            'status' => 'Draft',
            'created_by' => $admin->id,
        ]);

        // Engineer updates status to 'Planning' -> succeeds
        $response = $this->actingAs($engineer)->put("/cases/{$case->id}", [
            'case_number' => 'CASE-ENG-001',
            'patient_reference' => $case->patient_reference,
            'surgeon_name' => $case->surgeon_name,
            'implant_type' => $case->implant_type,
            'status' => 'Planning',
            'priority' => 'High',
            'surgery_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('cases.show', $case));
        $this->assertDatabaseHas('cases', [
            'id' => $case->id,
            'status' => 'Planning',
            'priority' => 'High',
        ]);

        // Engineer attempts to delete -> blocked with 403 Forbidden
        $deleteResponse = $this->actingAs($engineer)->delete("/cases/{$case->id}");
        $deleteResponse->assertStatus(403);

        $this->assertDatabaseHas('cases', [
            'id' => $case->id,
        ]);
    }

    /**
     * Test REST API Authentication flow (login, token issuance, and logout).
     */
    public function test_api_login_and_logout_flow(): void
    {
        $user = User::factory()->create([
            'email' => 'api.user@jajalmedical.com',
            'password' => bcrypt('SecretPassword123!'),
            'role' => 'admin',
        ]);

        // POST /api/login
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'api.user@jajalmedical.com',
            'password' => 'SecretPassword123!',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Authentication successful.',
            ])
            ->assertJsonStructure([
                'data' => ['token', 'token_type', 'user'],
            ]);

        $token = $loginResponse->json('data.token');
        $this->assertNotEmpty($token);

        // Authenticated request using Bearer token
        $casesResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/cases');

        $casesResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // POST /api/logout
        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'API token revoked successfully.',
            ]);
    }
}
