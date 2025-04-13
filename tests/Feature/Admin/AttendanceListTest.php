<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\Classroom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $lecturer;
    protected $student;
    protected $course;
    protected $classroom;
    protected $classSchedule;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users with different roles
        $this->admin = User::factory()->create(['role' => 'admin']);

        $lecturerUser = User::factory()->create(['role' => 'lecturer']);
        $this->lecturer = Lecturer::factory()->create(['user_id' => $lecturerUser->id]); // Associated by factory relationship

        // Create course & classroom
        $this->course = Course::factory()->create();
        $this->classroom = Classroom::factory()->create();

        // Create class schedule
        $this->classSchedule = ClassSchedule::factory()->create([
            'course_id' => $this->course->id,
            'lecturer_id' => $this->lecturer->id,
            'classroom_id' => $this->classroom->id,
        ]);

        // Create student in the classroom
        $studentUser = User::factory()->create(['role' => 'student']);
        $this->student = Student::factory()->create([
            'user_id' => $studentUser->id,
            'classroom_id' => $this->classroom->id,
        ]);

        // Create attendance record
        $this->attendance = Attendance::factory()->create([
            'class_schedule_id' => $this->classSchedule->id,
            'student_id' => $this->student->id,
            'date' => now()->format('Y-m-d'),
            'status' => 'absent',
        ]);
    }

    public function test_admin_can_view_attendance_list()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.attendance.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.attendance.index');
        $response->assertViewHas(['attendances', 'courses', 'students', 'statuses']);
        $response->assertSee($this->student->user->name);
    }

    public function test_non_admin_cannot_view_attendance_list()
    {
        $response = $this->actingAs($this->student->user)
            ->get(route('admin.attendance.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_filter_attendance_by_course()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.attendance.index', ['course_id' => $this->course->id]));

        $response->assertStatus(200);
        $response->assertViewHas('attendances');
        $response->assertSee($this->student->user->name);
    }

    public function test_admin_can_filter_attendance_by_date()
    {
        $date = now()->format('Y-m-d');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.attendance.index', ['date' => $date]));

        $response->assertStatus(200);
        $response->assertViewHas('attendances');
        $response->assertSee($this->student->user->name);
    }

    public function test_admin_can_filter_attendance_by_student()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.attendance.index', ['student_id' => $this->student->id]));

        $response->assertStatus(200);
        $response->assertViewHas('attendances');
        $response->assertSee($this->student->user->name);
    }

    public function test_admin_can_filter_attendance_by_status()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.attendance.index', ['status' => 'absent']));

        $response->assertStatus(200);
        $response->assertViewHas('attendances');
        $response->assertSee($this->student->user->name);
    }

    public function test_admin_can_update_attendance_status()
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.attendance.update-status'), [
                'attendance_id' => $this->attendance->id,
                'status' => 'present',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'present'
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $this->attendance->id,
            'status' => 'present'
        ]);
    }

    public function test_student_cannot_update_attendance_status()
    {
        $response = $this->actingAs($this->student->user)
            ->postJson(route('admin.attendance.update-status'), [
                'attendance_id' => $this->attendance->id,
                'status' => 'present',
            ]);

        $response->assertStatus(403);
    }

    public function test_attendance_status_must_be_valid()
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.attendance.update-status'), [
                'attendance_id' => $this->attendance->id,
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('status');
    }

    public function test_attendance_id_must_be_valid()
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.attendance.update-status'), [
                'attendance_id' => 9999, // Non-existent ID
                'status' => 'present',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('attendance_id');
    }

    public function test_attendance_update_handles_exceptions()
    {
        // Mock the repository to throw an exception
        $this->mock(
            \App\Repositories\Interfaces\AttendanceRepositoryInterface::class,
            function ($mock) {
                $mock->shouldReceive('findById')
                    ->andThrow(new \Exception('Database error'));
            }
        );

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.attendance.update-status'), [
                'attendance_id' => $this->attendance->id,
                'status' => 'present',
            ]);

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'Failed to update status: Database error'
        ]);
    }
}
