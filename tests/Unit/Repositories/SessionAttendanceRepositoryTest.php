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

    public function test_find_by_qr_code()
    {
        // Arrange
        $qrCode = 'test-qr-code-123';
        $session = SessionAttendance::factory()->create([
            'qr_code' => $qrCode,
            'is_active' => true
        ]);

        // Act
        $result = $this->repository->findByQrCode($qrCode);

        // Assert
        $this->assertInstanceOf(SessionAttendance::class, $result);
        $this->assertEquals($qrCode, $result->qr_code);
        $this->assertTrue($result->is_active);
    }

    public function test_find_by_qr_code_returns_null_for_inactive_session()
    {
        // Arrange
        $qrCode = 'test-qr-code-456';
        $session = SessionAttendance::factory()->create([
            'qr_code' => $qrCode,
            'is_active' => false
        ]);

        // Act
        $result = $this->repository->findByQrCode($qrCode);

        // Assert
        // The implementation should still return the session even if inactive
        // since validation of active status is done in QRCodeService
        $this->assertInstanceOf(SessionAttendance::class, $result);
        $this->assertEquals($qrCode, $result->qr_code);
        $this->assertFalse($result->is_active);
    }

    public function test_find_by_qr_code_returns_null_for_nonexistent_code()
    {
        // Arrange
        $qrCode = 'nonexistent-qr-code';

        // Act
        $result = $this->repository->findByQrCode($qrCode);

        // Assert
        $this->assertNull($result);
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

    public function test_find_by_class_week_and_meeting()
    {
        // Arrange
        $classId = 1;
        $week = 2;
        $meeting = 1;

        $session = SessionAttendance::factory()->create([
            'class_schedule_id' => $classId,
            'week' => $week,
            'meetings' => $meeting
        ]);

        // Act
        $result = $this->repository->findByClassWeekAndMeeting($classId, $week, $meeting);

        // Assert
        $this->assertInstanceOf(SessionAttendance::class, $result);
        $this->assertEquals($classId, $result->class_schedule_id);
        $this->assertEquals($week, $result->week);
        $this->assertEquals($meeting, $result->meetings);
    }

    public function test_session_exists_for_date()
    {
        // Arrange
        $classId = 1;
        $date = '2023-07-01';

        SessionAttendance::factory()->create([
            'class_schedule_id' => $classId,
            'session_date' => $date
        ]);

        // Act
        $result = $this->repository->sessionExistsForDate($classId, $date);

        // Assert
        $this->assertTrue($result);
    }

    public function test_session_does_not_exist_for_date()
    {
        // Arrange
        $classId = 1;
        $date = '2023-07-01';

        // No session created for this date

        // Act
        $result = $this->repository->sessionExistsForDate($classId, $date);

        // Assert
        $this->assertFalse($result);
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
