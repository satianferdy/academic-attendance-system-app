<?php

namespace Tests\Unit\Services;

use App\Models\ClassSchedule;
use App\Repositories\Interfaces\ClassScheduleRepositoryInterface;
use App\Services\Implementations\ScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class ScheduleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $classScheduleRepository;
    protected $scheduleService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock for the repository
        $this->classScheduleRepository = Mockery::mock(ClassScheduleRepositoryInterface::class);

        // Inject mock into service
        $this->scheduleService = new ScheduleService($this->classScheduleRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_returns_weekdays()
    {
        $weekdays = $this->scheduleService->getWeekdays();

        $this->assertCount(7, $weekdays);
        $this->assertEquals('Senin', $weekdays[0]);
        $this->assertEquals('Minggu', $weekdays[6]);
    }

    public function test_generates_time_slots()
    {
        $timeSlots = $this->scheduleService->generateTimeSlots();

        $this->assertCount(9, $timeSlots); // 7:00 to 16:00 should generate 9 slots
        $this->assertEquals('07:00 - 08:00', $timeSlots[0]);
        $this->assertEquals('15:00 - 16:00', $timeSlots[8]);
    }

    public function test_parses_time_slot_correctly()
    {
        $timeSlot = '09:00 - 10:00';
        $parsed = $this->scheduleService->parseTimeSlot($timeSlot);

        $this->assertCount(2, $parsed);
        $this->assertEquals('09:00', $parsed[0]);
        $this->assertEquals('10:00', $parsed[1]);
    }

    public function test_checks_time_slots_availability_and_returns_available()
    {
        // Test data
        $room = 'A101';
        $day = 'Senin';
        $timeSlots = ['09:00 - 10:00', '10:00 - 11:00'];
        $lecturerId = 1;

        // Setup expectations for repository mock
        $this->classScheduleRepository->shouldReceive('findConflictingTimeSlots')
            ->twice() // called for each time slot
            ->andReturn([
                'room' => [],
                'lecturer' => []
            ]);

        // Call the method
        $result = $this->scheduleService->checkAllTimeSlotsAvailability($room, $day, $timeSlots, $lecturerId);

        // Assert the result
        $this->assertTrue($result['available']);
    }

    public function test_checks_time_slots_availability_and_returns_not_available_due_to_room_conflict()
    {
        // Test data
        $room = 'A101';
        $day = 'Senin';
        $timeSlots = ['09:00 - 10:00'];
        $lecturerId = 1;

        // Mock schedule for conflict
        $schedule = Mockery::mock(ClassSchedule::class);

        // Setup expectations for repository mock
        $this->classScheduleRepository->shouldReceive('findConflictingTimeSlots')
            ->once()
            ->andReturn([
                'room' => [
                    [
                        'slot' => '09:00 - 10:00',
                        'schedule' => $schedule
                    ]
                ],
                'lecturer' => []
            ]);

        // Call the method
        $result = $this->scheduleService->checkAllTimeSlotsAvailability($room, $day, $timeSlots, $lecturerId);

        // Assert the result
        $this->assertFalse($result['available']);
        $this->assertEquals('room', $result['conflictType']);
        $this->assertContains('09:00 - 10:00', $result['unavailableSlots']);
        $this->assertStringContainsString('for this room', $result['message']);
    }

    public function test_checks_time_slots_availability_and_returns_not_available_due_to_lecturer_conflict()
    {
        // Test data
        $room = 'A101';
        $day = 'Senin';
        $timeSlots = ['09:00 - 10:00'];
        $lecturerId = 1;

        // Mock schedule for conflict
        $schedule = Mockery::mock(ClassSchedule::class);

        // Setup expectations for repository mock
        $this->classScheduleRepository->shouldReceive('findConflictingTimeSlots')
            ->once()
            ->andReturn([
                'room' => [],
                'lecturer' => [
                    [
                        'slot' => '09:00 - 10:00',
                        'schedule' => $schedule
                    ]
                ]
            ]);

        // Call the method
        $result = $this->scheduleService->checkAllTimeSlotsAvailability($room, $day, $timeSlots, $lecturerId);

        // Assert the result
        $this->assertFalse($result['available']);
        $this->assertEquals('lecturer', $result['conflictType']);
        $this->assertContains('09:00 - 10:00', $result['unavailableSlots']);
        $this->assertStringContainsString('for this lecturer', $result['message']);
    }

    public function test_creates_schedule_with_time_slots()
    {
        // Set up spy on DB facade transaction method instead of mocking the class
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        // Test data
        $scheduleData = [
            'course_id' => 1,
            'lecturer_id' => 1,
            'classroom_id' => 1,
            'room' => 'A101',
            'day' => 'Monday',
            'semester' => 'Odd',
            'academic_year' => '2022/2023',
            'time_slots' => ['09:00 - 10:00', '10:00 - 11:00']
        ];

        // Mock created schedule
        $schedule = Mockery::mock(ClassSchedule::class);
        $schedule->shouldReceive('timeSlots->create')
            ->twice()
            ->andReturn(true);

        // Setup expectations for repository mock
        $this->classScheduleRepository->shouldReceive('createSchedule')
            ->once()
            ->with($scheduleData)
            ->andReturn($schedule);

        // Call the method
        $result = $this->scheduleService->createScheduleWithTimeSlots($scheduleData);

        // Assert the result
        $this->assertSame($schedule, $result);
    }

    public function test_updates_schedule_with_time_slots()
    {
        // Set up spy on DB facade transaction method instead of mocking the class
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        // Test data
        $scheduleId = 1;
        $scheduleData = [
            'course_id' => 1,
            'lecturer_id' => 1,
            'classroom_id' => 1,
            'room' => 'A101',
            'day' => 'Monday',
            'semester' => 'Odd',
            'academic_year' => '2022/2023',
            'time_slots' => ['09:00 - 10:00', '10:00 - 11:00']
        ];

        // Create a proper mock for the schedule with required properties and methods
        $schedule = Mockery::mock(ClassSchedule::class);

        // Make sure id attribute returns a valid value
        $schedule->shouldReceive('getAttribute')->with('id')->andReturn($scheduleId);
        // Handle generic property access if needed
        $schedule->shouldReceive('getAttribute')->withAnyArgs()->andReturnNull();
        $schedule->shouldReceive('setAttribute')->withAnyArgs()->andReturnSelf();

        // Set up the timeSlots relationship mock
        $timeSlotsRelation = Mockery::mock();
        $timeSlotsRelation->shouldReceive('delete')->once()->andReturn(true);
        $timeSlotsRelation->shouldReceive('create')->twice()->andReturn(true);

        // Make sure the timeSlots() method returns the relation mock
        $schedule->shouldReceive('timeSlots')->andReturn($timeSlotsRelation);

        // Setup expectations for repository mock
        $this->classScheduleRepository->shouldReceive('updateSchedule')
            ->once()
            ->with($scheduleId, $scheduleData)
            ->andReturn(true);

        // Call the method
        $result = $this->scheduleService->updateScheduleWithTimeSlots($schedule, $scheduleData);

        // Assert the result
        $this->assertSame($schedule, $result);
    }

    public function test_gets_booked_time_slots()
    {
        // Test data
        $room = 'A101';
        $day = 'Monday';
        $lecturerId = 1;

        // Create a proper mock for start_time and end_time objects
        $startTime1 = Mockery::mock('DateTime');
        $startTime1->shouldReceive('format')->with('H:i')->andReturn('09:00');

        $endTime1 = Mockery::mock('DateTime');
        $endTime1->shouldReceive('format')->with('H:i')->andReturn('10:00');

        $startTime2 = Mockery::mock('DateTime');
        $startTime2->shouldReceive('format')->with('H:i')->andReturn('11:00');

        $endTime2 = Mockery::mock('DateTime');
        $endTime2->shouldReceive('format')->with('H:i')->andReturn('12:00');

        // Mock time slots as objects with properties
        $timeSlot1 = Mockery::mock();
        $timeSlot1->start_time = $startTime1;
        $timeSlot1->end_time = $endTime1;

        $timeSlot2 = Mockery::mock();
        $timeSlot2->start_time = $startTime2;
        $timeSlot2->end_time = $endTime2;

        // Mock user with name property
        $user = new \stdClass();
        $user->name = 'John Doe';

        // Mock lecturer with user property that's an object
        $lecturer = new \stdClass();
        $lecturer->user = $user;

        // Mock room schedule with properties instead of methods
        $roomSchedule = Mockery::mock(ClassSchedule::class)->makePartial();
        $roomSchedule->lecturer = $lecturer;
        $roomSchedule->timeSlots = [$timeSlot1];
        $roomSchedule->room = 'A101';

        // Mock lecturer schedule
        $lecturerSchedule = Mockery::mock(ClassSchedule::class)->makePartial();
        $lecturerSchedule->lecturer = $lecturer;
        $lecturerSchedule->timeSlots = [$timeSlot2];
        $lecturerSchedule->room = 'B202';

        // Setup expectations for repository mock
        $this->classScheduleRepository->shouldReceive('getSchedulesByRoomAndDay')
            ->once()
            ->with($room, $day, null)
            ->andReturn([$roomSchedule]);

        $this->classScheduleRepository->shouldReceive('getSchedulesByLecturerAndDay')
            ->once()
            ->with($lecturerId, $day, null)
            ->andReturn([$lecturerSchedule]);

        // Call the method
        $result = $this->scheduleService->getBookedTimeSlots($room, $day, $lecturerId);

        // Assert the result
        $this->assertCount(2, $result);
        $this->assertEquals('09:00', $result[0]['start_time']);
        $this->assertEquals('10:00', $result[0]['end_time']);
        $this->assertEquals('room', $result[0]['type']);
        $this->assertEquals('11:00', $result[1]['start_time']);
        $this->assertEquals('12:00', $result[1]['end_time']);
        $this->assertEquals('lecturer', $result[1]['type']);
        $this->assertEquals('B202', $result[1]['room']);
    }
}
