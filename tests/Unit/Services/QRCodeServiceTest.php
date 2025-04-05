<?php

namespace Tests\Unit\Services;

use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;
use App\Services\Implementations\QRCodeService;
use App\Models\SessionAttendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
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
        $tokenData = [
            'class_id' => $classId,
            'date' => $date,
            'timestamp' => now()->timestamp
        ];

        // Create encrypted token
        $token = Crypt::encrypt($tokenData);

        // Setup config
        Config::set('services.qrcode.expiry_time', 30);

        // Call the method
        $result = $this->qrCodeService->validateToken($token);

        // Assert the result
        $this->assertNotNull($result);
        $this->assertEquals($classId, $result['class_id']);
        $this->assertEquals($date, $result['date']);
    }

    /**
     * Test invalidating an expired token
     */
    public function test_invalidates_expired_token()
    {
        // Test data with timestamp more than expiry time ago
        $classId = 1;
        $date = '2023-07-01';
        $tokenData = [
            'class_id' => $classId,
            'date' => $date,
            'timestamp' => now()->subMinutes(35)->timestamp // Set to 35 minutes ago
        ];

        // Create encrypted token
        $token = Crypt::encrypt($tokenData);

        // Setup config with 30 minutes expiry
        Config::set('services.qrcode.expiry_time', 30);

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
