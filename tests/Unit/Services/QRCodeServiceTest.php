<?php

namespace Tests\Unit\Services;

use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;
use App\Services\Implementations\QRCodeService;
use App\Models\SessionAttendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Mockery;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeFacade;
use Tests\TestCase;

class QRCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $sessionRepository;
    protected $qrCodeService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock for repository
        $this->sessionRepository = Mockery::mock(SessionAttendanceRepositoryInterface::class);

        // Inject mock into service
        $this->qrCodeService = new QRCodeService($this->sessionRepository);
    }

    protected function tearDown(): void
    {
        // Make sure to reset any static mocks
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test generating QR code for attendance
     */
    public function test_generates_qr_code_for_attendance()
    {
        // Test data
        $classId = 1;
        $date = '2023-07-01';
        $qrSize = 300;
        $qrOutput = 'QR CODE DATA'; // Simulated QR code output

        // Mock session
        $session = Mockery::mock(SessionAttendance::class);

        // Use a dedicated mock instance instead of the facade directly
        $qrCodeMock = Mockery::mock('qrcode');
        $qrCodeMock->shouldReceive('size')
            ->once()
            ->with($qrSize)
            ->andReturnSelf();

        $qrCodeMock->shouldReceive('generate')
            ->once()
            ->andReturn($qrOutput);

        // Replace the facade with our mock
        QrCodeFacade::swap($qrCodeMock);

        // Setup config
        Config::set('services.qrcode.size', $qrSize);

        // Setup repository expectations
        $this->sessionRepository->shouldReceive('findByClassAndDate')
            ->once()
            ->with($classId, $date)
            ->andReturn($session);

        $this->sessionRepository->shouldReceive('update')
            ->once()
            ->with($session, Mockery::on(function ($data) {
                return isset($data['qr_code']) && $data['is_active'] === true;
            }))
            ->andReturn($session);

        // Call the method
        $result = $this->qrCodeService->generateForAttendance($classId, $date);

        // Assert the result
        $this->assertEquals($qrOutput, $result);
    }

    /**
     * Test validating a valid token
     */
    public function test_validates_valid_token()
    {
        // Test data
        $classId = 1;
        $date = '2023-07-01';
        $token = 'valid-token';

        // Mock session with active status and future end time
        $session = Mockery::mock(SessionAttendance::class);
        $session->shouldReceive('getAttribute')
            ->with('class_schedule_id')
            ->andReturn($classId);

        $session->shouldReceive('getAttribute')
            ->with('session_date')
            ->andReturn(Carbon::parse($date));

        $session->shouldReceive('getAttribute')
            ->with('is_active')
            ->andReturn(true);

        $session->shouldReceive('getAttribute')
            ->with('end_time')
            ->andReturn(Carbon::now()->addHours(1));

        // Setup repository expectations
        $this->sessionRepository->shouldReceive('findByQrCode')
            ->once()
            ->with($token)
            ->andReturn($session);

        // Call the method
        $result = $this->qrCodeService->validateToken($token);

        // Assert the result
        $this->assertNotNull($result);
        $this->assertEquals($classId, $result['class_id']);
        $this->assertEquals($date, $result['date']->format('Y-m-d'));
    }

    /**
     * Test invalidating an expired token
     */
    public function test_invalidates_expired_token()
    {
        // Test data with timestamp more than expiry time ago
        $token = 'expired-token';

        // Mock session with active status but expired end time
        $session = Mockery::mock(SessionAttendance::class);
        $session->shouldReceive('getAttribute')
            ->with('is_active')
            ->andReturn(true);

        $session->shouldReceive('getAttribute')
            ->with('end_time')
            ->andReturn(Carbon::now()->subMinutes(30));

        // Setup repository expectations
        $this->sessionRepository->shouldReceive('findByQrCode')
            ->once()
            ->with($token)
            ->andReturn($session);

        // Call the method
        $result = $this->qrCodeService->validateToken($token);

        // Assert the result
        $this->assertNull($result);
    }

    public function test_invalidates_inactive_token()
    {
        // Test data with inactive session
        $token = 'inactive-token';

        // Mock session with inactive status
        $session = Mockery::mock(SessionAttendance::class);
        $session->shouldReceive('getAttribute')
            ->with('is_active')
            ->andReturn(false);

        // Setup repository expectations
        $this->sessionRepository->shouldReceive('findByQrCode')
            ->once()
            ->with($token)
            ->andReturn($session);

        // Call the method
        $result = $this->qrCodeService->validateToken($token);

        // Assert the result
        $this->assertNull($result);
    }

    /**
     * Test handling an invalid token
     */
    public function test_handles_invalid_token()
    {
        // Create invalid token
        $token = 'invalid-token-data';

        // Call the method
        $result = $this->qrCodeService->validateToken($token);

        // Assert the result
        $this->assertNull($result);
    }
}
