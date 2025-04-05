<?php

namespace Tests\Unit\Repositories;

use App\Models\ClassSchedule;
use App\Models\SessionAttendance;
use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionAttendanceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(SessionAttendanceRepositoryInterface::class);
    }

    public function test_find_active_by_class_and_date_with_active_session()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();
        $date = '2023-08-15';

        $session = SessionAttendance::factory()->create([
            'class_schedule_id' => $classSchedule->id,
            'session_date' => $date,
            'is_active' => true,
        ]);

        // Act
        $result = $this->repository->findActiveByClassAndDate($classSchedule->id, $date);

        // Assert
        $this->assertInstanceOf(SessionAttendance::class, $result);
        $this->assertEquals($session->id, $result->id);
        $this->assertTrue($result->is_active);
    }

    public function test_find_active_by_class_and_date_with_inactive_session()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();
        $date = '2023-08-15';

        SessionAttendance::factory()->create([
            'class_schedule_id' => $classSchedule->id,
            'session_date' => $date,
            'is_active' => false,
        ]);

        // Act
        $result = $this->repository->findActiveByClassAndDate($classSchedule->id, $date);

        // Assert
        $this->assertNull($result);
    }

    public function test_create_or_update_when_creating_new_session()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();
        $date = '2023-08-15';

        $attributes = [
            'class_schedule_id' => $classSchedule->id,
            'session_date' => $date,
        ];

        $values = [
            'start_time' => '08:00',
            'end_time' => '10:00',
            'qr_code' => 'qrcode123',
            'is_active' => true,
        ];

        // Act
        $result = $this->repository->createOrUpdate($attributes, $values);

        // Assert
        $this->assertInstanceOf(SessionAttendance::class, $result);
        $this->assertEquals($classSchedule->id, $result->class_schedule_id);
        $this->assertEquals($date, $result->session_date->format('Y-m-d'));
        $this->assertEquals('qrcode123', $result->qr_code);

        // Verify a new record was created
        $this->assertEquals(1, SessionAttendance::count());
    }

    public function test_create_or_update_with_existing_session()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();
        $date = '2023-08-15';

        // Create an existing session
        SessionAttendance::factory()->create([
            'class_schedule_id' => $classSchedule->id,
            'session_date' => $date,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'qr_code' => 'oldqr123',
            'is_active' => false,
        ]);

        $attributes = [
            'class_schedule_id' => $classSchedule->id,
            'session_date' => $date,
        ];

        $values = [
            'start_time' => '09:00',
            'end_time' => '11:00',
            'qr_code' => 'newqr456',
            'is_active' => true,
        ];

        // Act
        $result = $this->repository->createOrUpdate($attributes, $values);

        // Assert
        $this->assertInstanceOf(SessionAttendance::class, $result);
        $this->assertEquals('oldqr123', $result->qr_code); // Using firstOrCreate, so values don't update
        $this->assertFalse($result->is_active);

        // Verify no new record was created
        $this->assertEquals(1, SessionAttendance::count());
    }

    public function test_create()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();
        $data = [
            'class_schedule_id' => $classSchedule->id,
            'session_date' => '2023-08-15',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'qr_code' => 'qrcode123',
            'is_active' => true,
        ];

        // Act
        $result = $this->repository->create($data);

        // Assert
        $this->assertInstanceOf(SessionAttendance::class, $result);
        $this->assertEquals($classSchedule->id, $result->class_schedule_id);
        $this->assertEquals('qrcode123', $result->qr_code);
        $this->assertTrue($result->is_active);
    }

    public function test_update()
    {
        // Arrange
        $session = SessionAttendance::factory()->create([
            'start_time' => '08:00',
            'end_time' => '10:00',
            'qr_code' => 'oldqr123',
            'is_active' => true,
        ]);

        $data = [
            'start_time' => '09:00',
            'end_time' => '11:00',
            'qr_code' => 'newqr456',
            'is_active' => false,
        ];

        // Act
        $result = $this->repository->update($session, $data);

        // Assert
        $this->assertInstanceOf(SessionAttendance::class, $result);
        $this->assertEquals('09:00', $result->start_time->format('H:i'));
        $this->assertEquals('11:00', $result->end_time->format('H:i'));
        $this->assertEquals('newqr456', $result->qr_code);
        $this->assertFalse($result->is_active);
    }

    public function test_find_by_class_and_date()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();
        $date = '2023-08-15';

        $session = SessionAttendance::factory()->create([
            'class_schedule_id' => $classSchedule->id,
            'session_date' => $date,
        ]);

        // Create another session for a different date
        SessionAttendance::factory()->create([
            'class_schedule_id' => $classSchedule->id,
            'session_date' => '2023-08-16',
        ]);

        // Act
        $result = $this->repository->findByClassAndDate($classSchedule->id, $date);

        // Assert
        $this->assertInstanceOf(SessionAttendance::class, $result);
        $this->assertEquals($session->id, $result->id);
    }

    public function test_deactivate_session()
    {
        // Arrange
        $session = SessionAttendance::factory()->create([
            'is_active' => true,
        ]);

        // Act
        $result = $this->repository->deactivateSession($session->id);
        $session->refresh();

        // Assert
        $this->assertTrue($result > 0); // Should return number of affected rows
        $this->assertFalse($session->is_active);
    }
}
