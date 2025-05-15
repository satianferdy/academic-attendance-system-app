<?php

namespace Tests\Unit\Policies;

use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\SessionAttendance;
use App\Models\Student;
use App\Models\User;
use App\Policies\AttendancePolicy;
use App\Policies\ClassSchedulePolicy;
use App\Policies\SessionAttendancePolicy;
use App\Policies\StudentPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\RefreshPermissions;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase, RefreshPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupPermissions();
    }

    // ===== ATTENDANCE POLICY TESTS =====

    public function test_attendance_policy_view_permissions()
    {
        // Create users with different roles
        $adminUser = User::factory()->create(['role' => 'admin']);
        $adminUser->assignRole('admin');

        $lecturerUser = User::factory()->create(['role' => 'lecturer']);
        $lecturerUser->assignRole('lecturer');
        $lecturer = \App\Models\Lecturer::factory()->create(['user_id' => $lecturerUser->id]);

        $studentUser = User::factory()->create(['role' => 'student']);
        $studentUser->assignRole('student');
        $student = Student::factory()->create(['user_id' => $studentUser->id]);

        $otherStudentUser = User::factory()->create(['role' => 'student']);
        $otherStudentUser->assignRole('student');
        $otherStudent = Student::factory()->create(['user_id' => $otherStudentUser->id]);

        // Create class schedule with lecturer
        $classSchedule = ClassSchedule::factory()->create(['lecturer_id' => $lecturer->id]);

        // Create attendance for student
        $attendance = Attendance::factory()->create([
            'class_schedule_id' => $classSchedule->id,
            'student_id' => $student->id
        ]);

        $policy = new AttendancePolicy();

        // Admin can view any attendance
        $this->assertTrue($policy->view($adminUser, $attendance));

        // Lecturer can view attendance for their class
        $this->assertTrue($policy->view($lecturerUser, $attendance));

        // Student can view their own attendance
        $this->assertTrue($policy->view($studentUser, $attendance));

        // Student cannot view other student's attendance
        $this->assertFalse($policy->view($otherStudentUser, $attendance));

        // Anyone can view lists of attendances (viewAny) with proper roles
        $this->assertTrue($policy->viewAny($adminUser));
        $this->assertTrue($policy->viewAny($lecturerUser));
        $this->assertTrue($policy->viewAny($studentUser));
    }

    public function test_attendance_policy_update_permissions()
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $adminUser->assignRole('admin');

        $lecturerUser = User::factory()->create(['role' => 'lecturer']);
        $lecturerUser->assignRole('lecturer');
        $lecturer = \App\Models\Lecturer::factory()->create(['user_id' => $lecturerUser->id]);

        $otherLecturerUser = User::factory()->create(['role' => 'lecturer']);
        $otherLecturerUser->assignRole('lecturer');
        $otherLecturer = \App\Models\Lecturer::factory()->create(['user_id' => $otherLecturerUser->id]);

        $studentUser = User::factory()->create(['role' => 'student']);
        $studentUser->assignRole('student');

        // Create class schedule with lecturer
        $classSchedule = ClassSchedule::factory()->create(['lecturer_id' => $lecturer->id]);
        $otherClassSchedule = ClassSchedule::factory()->create(['lecturer_id' => $otherLecturer->id]);

        // Create attendance
        $attendance = Attendance::factory()->create([
            'class_schedule_id' => $classSchedule->id
        ]);

        //assert false for view for ather attendance
        $otherAttendance = Attendance::factory()->create([
            'class_schedule_id' => $otherClassSchedule->id
        ]);

        $policy = new AttendancePolicy();

        $this->assertFalse($policy->view($studentUser, $otherAttendance));
        $this->assertFalse($policy->view($lecturerUser, $otherAttendance));

        // Admin can update any attendance
        $this->assertTrue($policy->update($adminUser, $attendance));

        // Lecturer can update attendance for their class
        $this->assertTrue($policy->update($lecturerUser, $attendance));

        // Student cannot update attendance
        $this->assertFalse($policy->update($studentUser, $attendance));

        //manageStudentAttendance permission
        $this->assertTrue($policy->manageStudentAttendance($adminUser, $attendance));
        $this->assertTrue($policy->manageStudentAttendance($lecturerUser, $attendance));
        $this->assertFalse($policy->manageStudentAttendance($studentUser, $attendance));
        $this->assertFalse($policy->manageStudentAttendance($lecturerUser, $otherAttendance));
    }

    // ===== CLASS SCHEDULE POLICY TESTS =====

    public function test_class_schedule_policy_permissions()
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $adminUser->assignRole('admin');

        $lecturerUser = User::factory()->create(['role' => 'lecturer']);
        $lecturerUser->assignRole('lecturer');
        $lecturer = \App\Models\Lecturer::factory()->create(['user_id' => $lecturerUser->id]);

        $otherLecturerUser = User::factory()->create(['role' => 'lecturer']);
        $otherLecturerUser->assignRole('lecturer');
        $otherLecturer = \App\Models\Lecturer::factory()->create(['user_id' => $otherLecturerUser->id]);

        $studentUser = User::factory()->create(['role' => 'student']);
        $studentUser->assignRole('student');
        $student = Student::factory()->create(['user_id' => $studentUser->id]);

        $otherStudentUser = User::factory()->create(['role' => 'student']);
        $otherStudentUser->assignRole('student');

        // Create classroom and link to student
        $classroom = \App\Models\ClassRoom::factory()->create();
        $student->classroom_id = $classroom->id;
        $student->save();

        // Create schedule for lecturer with the classroom
        $classSchedule = ClassSchedule::factory()->create([
            'lecturer_id' => $lecturer->id,
            'classroom_id' => $classroom->id
        ]);

        // Create schedule for other lecturer
        $otherSchedule = ClassSchedule::factory()->create([
            'lecturer_id' => $otherLecturer->id
        ]);

        $policy = new ClassSchedulePolicy();

        // Test view permission
        $this->assertTrue($policy->view($adminUser, $classSchedule));
        $this->assertTrue($policy->view($lecturerUser, $classSchedule)); // Own schedule
        $this->assertFalse($policy->view($lecturerUser, $otherSchedule)); // Not own schedule
        $this->assertTrue($policy->view($studentUser, $classSchedule)); // Enrolled in class
        $this->assertFalse($policy->view($otherStudentUser, $classSchedule)); // Not enrolled in class

        // Test viewAny permission
        $this->assertTrue($policy->viewAny($adminUser));
        $this->assertTrue($policy->viewAny($lecturerUser));
        $this->assertTrue($policy->viewAny($studentUser));

        // Test view permission
        $this->assertTrue($policy->view($adminUser, $classSchedule));
        $this->assertTrue($policy->view($lecturerUser, $classSchedule)); // Own schedule
        $this->assertFalse($policy->view($lecturerUser, $otherSchedule)); // Not own schedule
        $this->assertTrue($policy->view($studentUser, $classSchedule)); // Enrolled in class

        // Test create permission
        $this->assertTrue($policy->create($adminUser));
        $this->assertTrue($policy->create($lecturerUser));
        $this->assertFalse($policy->create($studentUser));

        // Test manage permission
        $this->assertTrue($policy->manage($adminUser, $classSchedule));
        $this->assertTrue($policy->manage($lecturerUser, $classSchedule)); // Own schedule
        $this->assertFalse($policy->manage($lecturerUser, $otherSchedule)); // Not own schedule
        $this->assertFalse($policy->manage($studentUser, $classSchedule));

        // Test update permission
        $this->assertTrue($policy->update($adminUser, $classSchedule));
        $this->assertTrue($policy->update($lecturerUser, $classSchedule)); // Own schedule
        $this->assertFalse($policy->update($lecturerUser, $otherSchedule)); // Not own schedule
        $this->assertFalse($policy->update($studentUser, $classSchedule));

        // Test delete permission
        $this->assertTrue($policy->delete($adminUser, $classSchedule));
        $this->assertTrue($policy->delete($lecturerUser, $classSchedule)); // Own schedule
        $this->assertFalse($policy->delete($lecturerUser, $otherSchedule)); // Not own schedule
        $this->assertFalse($policy->delete($studentUser, $classSchedule));

        // Test QR generation and time extension permissions
        $this->assertTrue($policy->generateQR($adminUser, $classSchedule));
        $this->assertTrue($policy->generateQR($lecturerUser, $classSchedule)); // Own schedule
        $this->assertFalse($policy->generateQR($lecturerUser, $otherSchedule)); // Not own schedule
        $this->assertFalse($policy->generateQR($studentUser, $classSchedule));

        $this->assertTrue($policy->extendTime($adminUser, $classSchedule));
        $this->assertTrue($policy->extendTime($lecturerUser, $classSchedule)); // Own schedule
        $this->assertFalse($policy->extendTime($lecturerUser, $otherSchedule)); // Not own schedule
        $this->assertFalse($policy->extendTime($studentUser, $classSchedule));
    }

    // ===== SESSION ATTENDANCE POLICY TESTS =====

    public function test_session_attendance_policy_permissions()
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $adminUser->assignRole('admin');

        $lecturerUser = User::factory()->create(['role' => 'lecturer']);
        $lecturerUser->assignRole('lecturer');
        $lecturer = \App\Models\Lecturer::factory()->create(['user_id' => $lecturerUser->id]);

        $otherLecturerUser = User::factory()->create(['role' => 'lecturer']);
        $otherLecturerUser->assignRole('lecturer');
        $otherLecturer = \App\Models\Lecturer::factory()->create(['user_id' => $otherLecturerUser->id]);

        $studentUser = User::factory()->create(['role' => 'student']);
        $studentUser->assignRole('student');
        $student = Student::factory()->create(['user_id' => $studentUser->id]);

        $otherStudentUser = User::factory()->create(['role' => 'student']);
        $otherStudentUser->assignRole('student');

        // Create classroom and link to student
        $classroom = \App\Models\ClassRoom::factory()->create();
        $student->classroom_id = $classroom->id;
        $student->save();

        // Create schedules
        $classSchedule = ClassSchedule::factory()->create([
            'lecturer_id' => $lecturer->id,
            'classroom_id' => $classroom->id
        ]);

        // Create session attendance
        $sessionAttendance = SessionAttendance::factory()->create([
            'class_schedule_id' => $classSchedule->id
        ]);

        $policy = new SessionAttendancePolicy();

        // Test view permission
        $this->assertTrue($policy->view($adminUser, $sessionAttendance));
        $this->assertTrue($policy->view($lecturerUser, $sessionAttendance)); // Own class
        $this->assertFalse($policy->view($otherLecturerUser, $sessionAttendance)); // Not own class
        $this->assertTrue($policy->view($studentUser, $sessionAttendance)); // Enrolled in class
        $this->assertFalse($policy->view($otherStudentUser, $sessionAttendance)); // Not enrolled in class

        // Test create permission
        $this->assertTrue($policy->create($adminUser, $classSchedule->id));
        $this->assertTrue($policy->create($lecturerUser, $classSchedule->id)); // Own class
        $this->assertFalse($policy->create($lecturerUser, null)); // No class schedule id
        $this->assertFalse($policy->create($otherLecturerUser, $classSchedule->id)); // Not own class
        $this->assertFalse($policy->create($studentUser, $classSchedule->id));

        // Test extend permission - using non-past session
        $sessionAttendance->end_time = now()->addHour();
        $this->assertTrue($policy->extend($adminUser, $sessionAttendance));
        $this->assertTrue($policy->extend($lecturerUser, $sessionAttendance)); // Own class
        $this->assertFalse($policy->extend($otherLecturerUser, $sessionAttendance)); // Not own class
        $this->assertFalse($policy->extend($studentUser, $sessionAttendance));

        // Test extend permission with past session (should fail)
        $sessionAttendance->end_time = now()->subHour();
        $this->assertFalse($policy->extend($adminUser, $sessionAttendance));
        $this->assertFalse($policy->extend($lecturerUser, $sessionAttendance));

        // Test close permission
        $this->assertTrue($policy->close($adminUser, $sessionAttendance));
        $this->assertTrue($policy->close($lecturerUser, $sessionAttendance)); // Own class
        $this->assertFalse($policy->close($otherLecturerUser, $sessionAttendance)); // Not own class
        $this->assertFalse($policy->close($studentUser, $sessionAttendance));
    }

    // ===== STUDENT POLICY TESTS =====

    public function test_student_policy_permissions()
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $adminUser->assignRole('admin');

        $lecturerUser = User::factory()->create(['role' => 'lecturer']);
        $lecturerUser->assignRole('lecturer');

        $studentUser = User::factory()->create(['role' => 'student']);
        $studentUser->assignRole('student');
        $student = Student::factory()->create(['user_id' => $studentUser->id]);

        $otherStudentUser = User::factory()->create(['role' => 'student']);
        $otherStudentUser->assignRole('student');
        $otherStudent = Student::factory()->create(['user_id' => $otherStudentUser->id]);

        $policy = new StudentPolicy();

        // Test update permission
        $this->assertTrue($policy->update($studentUser, $student)); // Own student record
        $this->assertFalse($policy->update($studentUser, $otherStudent)); // Not own student record
        $this->assertFalse($policy->update($lecturerUser, $student));

        // Test viewFaceImages permission
        $this->assertTrue($policy->viewFaceImages($adminUser)); // Only admin can view face images
        $this->assertFalse($policy->viewFaceImages($lecturerUser));
        $this->assertFalse($policy->viewFaceImages($studentUser));
    }

    // ===== USER POLICY TESTS =====

    public function test_user_policy_permissions()
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $adminUser->assignRole('admin');
        $adminUser->givePermissionTo('manage users');

        $regularUser = User::factory()->create(['role' => 'lecturer']);
        $regularUser->assignRole('lecturer');

        $anotherRegularUser = User::factory()->create(['role' => 'student']);
        $anotherRegularUser->assignRole('student');

        $policy = new UserPolicy();

        // Test viewAny permission
        $this->assertTrue($policy->viewAny($adminUser)); // Admin with permission
        $this->assertFalse($policy->viewAny($regularUser)); // Regular user without permission

        // Test view permission
        $this->assertTrue($policy->view($adminUser, $regularUser)); // Admin can view any user
        $this->assertTrue($policy->view($regularUser, $regularUser)); // User can view self
        $this->assertFalse($policy->view($regularUser, $anotherRegularUser)); // User cannot view others

        // Test create permission
        $this->assertTrue($policy->create($adminUser)); // Admin with permission
        $this->assertFalse($policy->create($regularUser)); // Regular user without permission

        // Test update permission
        $this->assertTrue($policy->update($adminUser, $regularUser)); // Admin can update any user
        $this->assertTrue($policy->update($regularUser, $regularUser)); // User can update self
        $this->assertFalse($policy->update($regularUser, $anotherRegularUser)); // User cannot update others

        // Test delete permission
        $this->assertTrue($policy->delete($adminUser, $regularUser)); // Admin can delete others
        $this->assertFalse($policy->delete($adminUser, $adminUser)); // Admin cannot delete self
        $this->assertFalse($policy->delete($regularUser, $anotherRegularUser)); // User cannot delete others
    }
}
