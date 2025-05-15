<?php

namespace Tests\Feature\Admin;

use App\Models\FaceUpdateRequest;
use App\Models\Student;
use App\Models\User;
use Tests\RefreshPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaceUpdateRequestTest extends TestCase
{
    use RefreshDatabase, RefreshPermissions;

    protected $admin;
    protected $student;
    protected $faceUpdateRequest;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions and roles for this test
        $this->setupPermissions();

        // Create admin user
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole('admin');

        // Create student with user
        $studentUser = User::factory()->create(['role' => 'student']);
        $studentUser->assignRole('student');
        $this->student = Student::factory()->create([
            'user_id' => $studentUser->id,
            'face_registered' => true
        ]);

        // Create a pending face update request
        $this->faceUpdateRequest = FaceUpdateRequest::create([
            'student_id' => $this->student->id,
            'reason' => 'My face has changed',
            'status' => 'pending'
        ]);
    }

    public function test_admin_can_view_face_update_requests()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.face-requests.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.face-requests.index');
        $response->assertViewHas('requests');
        $response->assertSee('My face has changed');
    }

    public function test_admin_can_view_face_update_requests_filtered_by_status()
    {
        // Create an approved request
        FaceUpdateRequest::create([
            'student_id' => $this->student->id,
            'reason' => 'Approved request',
            'status' => 'approved',
            'approved_by' => $this->admin->id,
            'approved_at' => now()
        ]);

        // Test filtering by pending status
        $response = $this->actingAs($this->admin)
            ->get(route('admin.face-requests.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('My face has changed');
        $response->assertDontSee('Approved request');

        // Test filtering by approved status
        $response = $this->actingAs($this->admin)
            ->get(route('admin.face-requests.index', ['status' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('Approved request');
        $response->assertDontSee('My face has changed');

        // Test viewing all statuses
        $response = $this->actingAs($this->admin)
            ->get(route('admin.face-requests.index', ['status' => 'all']));

        $response->assertStatus(200);
        $response->assertSee('My face has changed');
        $response->assertSee('Approved request');
    }

    public function test_admin_can_approve_face_update_request()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.face-requests.approve', $this->faceUpdateRequest), [
                'admin_notes' => 'Request approved by admin',
            ]);

        $response->assertRedirect(route('admin.face-requests.index'));
        $response->assertSessionHas('success', 'Face update request has been approved.');

        $this->faceUpdateRequest->refresh();
        $this->assertEquals('approved', $this->faceUpdateRequest->status);
        $this->assertEquals('Request approved by admin', $this->faceUpdateRequest->admin_notes);
        $this->assertEquals($this->admin->id, $this->faceUpdateRequest->approved_by);
        $this->assertNotNull($this->faceUpdateRequest->approved_at);
    }

    public function test_admin_can_reject_face_update_request()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.face-requests.reject', $this->faceUpdateRequest), [
                'admin_notes' => 'Request rejected due to invalid reason',
            ]);

        $response->assertRedirect(route('admin.face-requests.index'));
        $response->assertSessionHas('success', 'Face update request has been rejected.');

        $this->faceUpdateRequest->refresh();
        $this->assertEquals('rejected', $this->faceUpdateRequest->status);
        $this->assertEquals('Request rejected due to invalid reason', $this->faceUpdateRequest->admin_notes);
        $this->assertEquals($this->admin->id, $this->faceUpdateRequest->approved_by);
        $this->assertNotNull($this->faceUpdateRequest->approved_at);
    }

    public function test_admin_notes_required_when_rejecting_request()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.face-requests.reject', $this->faceUpdateRequest), [
                'admin_notes' => '',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('admin_notes');

        $this->faceUpdateRequest->refresh();
        $this->assertEquals('pending', $this->faceUpdateRequest->status);
    }

    public function test_non_admin_cannot_view_face_update_requests()
    {
        // Create a lecturer user
         /** @var \App\Models\User $lecturer */
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $lecturer->assignRole('lecturer');

        $response = $this->actingAs($lecturer)
            ->get(route('admin.face-requests.index'));

        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_approve_face_update_requests()
    {
        // Use the student user
        $response = $this->actingAs($this->student->user)
            ->post(route('admin.face-requests.approve', $this->faceUpdateRequest), [
                'admin_notes' => 'Trying to approve my own request',
            ]);

        $response->assertStatus(403);

        $this->faceUpdateRequest->refresh();
        $this->assertEquals('pending', $this->faceUpdateRequest->status);
    }
}
