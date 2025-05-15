<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use App\Repositories\Implementations\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        // Create repository instance
        $this->repository = new UserRepository(
            new User(),
            new Student(),
            new Lecturer()
        );
    }

    public function test_find_user_by_id()
    {
        // Create a user without relations
        $regularUser = User::factory()->create([
            'name' => 'Regular User',
            'role' => 'admin'
        ]);

        // Create a user with student relation
        $studentUser = User::factory()->create([
            'name' => 'Student User',
            'role' => 'student'
        ]);
        $student = Student::factory()->create(['user_id' => $studentUser->id]);

        // Create a user with lecturer relation
        $lecturerUser = User::factory()->create([
            'name' => 'Lecturer User',
            'role' => 'lecturer'
        ]);
        $lecturer = Lecturer::factory()->create(['user_id' => $lecturerUser->id]);

        // Test finding regular user
        $foundUser = $this->repository->findUserById($regularUser->id);
        $this->assertNotNull($foundUser);
        $this->assertEquals('Regular User', $foundUser->name);
        $this->assertNull($foundUser->student);
        $this->assertNull($foundUser->lecturer);

        // Test finding student user with eager-loaded relation
        $foundStudentUser = $this->repository->findUserById($studentUser->id);
        $this->assertNotNull($foundStudentUser);
        $this->assertEquals('Student User', $foundStudentUser->name);
        $this->assertNotNull($foundStudentUser->student);
        $this->assertEquals($student->id, $foundStudentUser->student->id);

        // Test finding lecturer user with eager-loaded relation
        $foundLecturerUser = $this->repository->findUserById($lecturerUser->id);
        $this->assertNotNull($foundLecturerUser);
        $this->assertEquals('Lecturer User', $foundLecturerUser->name);
        $this->assertNotNull($foundLecturerUser->lecturer);
        $this->assertEquals($lecturer->id, $foundLecturerUser->lecturer->id);

        // Test finding non-existent user
        $nonExistentUser = $this->repository->findUserById(999);
        $this->assertNull($nonExistentUser);
    }
}
