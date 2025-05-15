<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\FaceUpdateRequest;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\RefreshPermissions;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase, WithFaker, RefreshPermissions;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup permissions and roles
        $this->setupPermissions();

        // Create admin user
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole('admin');
    }

    /**
     * Test admin can access dashboard
     */
    public function test_admin_can_access_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    /**
     * Test dashboard displays face registration data
     */
    public function test_dashboard_displays_face_registration_data()
    {
        // Create students with varied face registration status
        Student::factory()->count(3)->create(['face_registered' => true]);
        Student::factory()->count(2)->create(['face_registered' => false]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('faceRegistration');

        $faceData = $response->viewData('faceRegistration');

        $this->assertEquals(5, $faceData['totalStudents']);
        $this->assertEquals(3, $faceData['studentsWithFace']);
        $this->assertEquals(60, $faceData['faceRegistrationPercentage']); // 3/5 = 60%
    }

    /**
     * Test dashboard displays weekly attendance data
     */
    public function test_dashboard_displays_weekly_attendance_data()
    {
        // Create attendance records with different statuses over the past week
        $statuses = ['present', 'absent', 'late', 'excused'];
        $students = Student::factory()->count(3)->create();

        // Create attendance records for the past 7 days
        for ($day = 0; $day < 7; $day++) {
            $date = Carbon::now()->subDays($day);

            foreach ($students as $index => $student) {
                // Ensure some variety in statuses
                $statusIndex = ($index + $day) % count($statuses);

                Attendance::factory()->create([
                    'student_id' => $student->id,
                    'date' => $date,
                    'status' => $statuses[$statusIndex]
                ]);
            }
        }

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('attendanceData');

        $attendanceData = $response->viewData('attendanceData');

        // Assert that the structure is correct
        $this->assertIsArray($attendanceData['weekDays']);
        $this->assertCount(7, $attendanceData['weekDays']);
        $this->assertIsArray($attendanceData['series']);
        $this->assertCount(4, $attendanceData['series']); // 4 status types

        // Check series names
        $seriesNames = array_column($attendanceData['series'], 'name');
        $this->assertContains('Hadir', $seriesNames);
        $this->assertContains('Tidak Hadir', $seriesNames);
        $this->assertContains('Terlambat', $seriesNames);
        $this->assertContains('Izin', $seriesNames);
    }

    /**
     * Test dashboard displays recent attendances
     */
    public function test_dashboard_displays_recent_attendances()
    {
        // Create some students and class schedules
        $students = Student::factory()->count(3)->create();

        // Create 10 attendance records with varied dates
        foreach (range(1, 10) as $i) {
            $date = Carbon::now()->subDays($i % 7); // Mix of recent dates

            Attendance::factory()->create([
                'student_id' => $students->random()->id,
                'date' => $date,
                'status' => $this->faker->randomElement(['present', 'absent', 'late', 'excused'])
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('recentAttendances');

        $recentAttendances = $response->viewData('recentAttendances');

        // By default, it should show 5 recent attendances
        $this->assertCount(5, $recentAttendances);

        // Verify the attendances are sorted by date (newest first)
        $dates = $recentAttendances->pluck('date')->map(function($date) {
            return $date->timestamp;
        })->toArray();

        $sortedDates = $dates;
        rsort($sortedDates);

        $this->assertEquals($sortedDates, $dates);
    }

    /**
     * Test non-admin users cannot access the dashboard
     */
    public function test_non_admin_cannot_access_dashboard()
    {
        // Create student and lecturer users
        /** @var \App\Models\User $student */
        $student = User::factory()->create(['role' => 'student']);
        $student->assignRole('student');

        /** @var \App\Models\User $lecturer */
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $lecturer->assignRole('lecturer');

        // Test student cannot access
        $response = $this->actingAs($student)
            ->get(route('admin.dashboard'));

        $response->assertStatus(403);

        // Test lecturer cannot access
        $response = $this->actingAs($lecturer)
            ->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    /**
     * Test dashboard with empty database
     */
    public function test_dashboard_with_empty_database()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);

        $statistics = $response->viewData('statistics');

        // All counts should be zero
        $this->assertEquals(0, $statistics['totalStudents']);
        $this->assertEquals(0, $statistics['totalLecturers']);
        $this->assertEquals(0, $statistics['totalCourses']);
        $this->assertEquals(0, $statistics['totalClassrooms']);
        $this->assertEquals(0, $statistics['todayAttendanceCount']);

        // Face registration percentage should handle division by zero
        $faceData = $response->viewData('faceRegistration');
        $this->assertEquals(0, $faceData['faceRegistrationPercentage']);
    }
}
