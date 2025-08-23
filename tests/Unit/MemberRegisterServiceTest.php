<?php

namespace Tests\Unit;

use Mockery;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Mockery\MockInterface;
use App\Models\User;
use App\Models\Guest;
use App\Services\MemberRegister\MemberRegisterService;
use App\Services\MemberRegister\UnitRegisterService;
use App\Services\Strategies\Verification\VerificationEmailOrchestrator;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;



class MemberRegisterServiceTest extends TestCase
{
    protected MockInterface $orchestratorMock;
    protected MockInterface $unitServiceMock;
    protected MemberRegisterService $service;

    protected function setUp(): void
    {
        /** @var MockInterface&VerificationEmailOrchestrator */
        $this->orchestratorMock = Mockery::mock(VerificationEmailOrchestrator::class);

        /** @var MockInterface&UnitRegisterService */
        $this->unitServiceMock = Mockery::mock(UnitRegisterService::class);

        $this->service = new MemberRegisterService(
            $this->orchestratorMock,
            $this->unitServiceMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ---------------- initiateRegistrationProcess ----------------

    /**
     * 測試：當 initiateRegistrationProcess 成功時
     */
    #[Test]
    public function initiateRegistrationProcess_passes_onSuccessfulDispatch(): void
    {
        $request = Request::create('/register', 'POST');

        // Arrange mocks
        $this->mockDispatchVerification($request);

        // Act
        $this->executeInitiateRegistrationProcess($request);
    }

    /**
     * 測試：當 dispatchVerification 失敗時
     */
    #[Test]
    public function initiateRegistrationProcess_throwsException_onDispatchException(): void
    {
        $errorMessage = '電子郵件寄送失敗';
        $request = Request::create('/register', 'POST');
        
        // Arrange mocks
        $this->mockDispatchVerification($request, $errorMessage);

        //Act
        $this->executeInitiateRegistrationProcess($request, $errorMessage);
    }

    // ---------------- authorizeSetPasswordPage ----------------

    /**
     * 測試：當 authorizeSetPasswordPage 成功時
     */
    #[Test]
    public function authorizeSetPasswordPage_passes_onAllValidationSuccessful(): void
    {
        $email = 'test@example.com';
        $token = 'test-token';
        
        // Arrange mocks
        $this->mockEnsureAccountValid($email);
        $this->mockVerifyRegisterToken($email, $token);
        
        // Act
        $this->executeAuthorizeSetPasswordPage($email, $token);
    }

    /**
     * 測試：當 ensureAccountValid 失敗時
     */
    #[Test]
    public function authorizeSetPasswordPage_throwsException_onEnsureException(): void
    {
        $errorMessage = '輸入帳號無效';
        $email = 'test@example.com';
        $token = 'test-token';
        
        // Arrange mocks
        $this->mockEnsureAccountValid($email, $errorMessage);

        // Act
        $this->executeAuthorizeSetPasswordPage($email, $token, $errorMessage);
    }

    /**
     * 測試：當 verifyRegisterToken 失敗時
     */
    #[Test]
    public function authorizeSetPasswordPage_throwsException_onVerifyTokenException(): void
    {
        $errorMessage = '訪客資料異常';
        $email = 'test@example.com';
        $token = 'test-token';
        
        // Arrange mocks
        $this->mockEnsureAccountValid($email);
        $this->mockVerifyRegisterToken($email, $token, $errorMessage);

        // Act
        $this->executeAuthorizeSetPasswordPage($email, $token, $errorMessage);
    }

    // ---------------- completeRegistrationProcess ----------------

    /**
     * 測試：當 completeRegistrationProcess 成功時
     */
    #[Test]
    public function completeRegistrationProcess_passes_onAllValidationSuccessful(): void
    {
        $email     = 'test@example.com';
        $password  = 'Test0000';
        $confirmed = 'Test0000';

        $userMock = Mockery::mock(User::class)->makePartial();
        $userMock->bearer_token = 'MockBearerToken';
        $cookie = new Cookie('bearer_token', $userMock->bearer_token);

        // Arrange mocks
        $this->mockEnsureDataValid([
            'email' => $email, 'password' => $password, 'guest' => Mockery::mock(Guest::class)
        ]);
        $this->mockCreateMember($userMock);
        $this->mockSetCookie($cookie);

        // Act
        $actualCookie =  $this->executeCompleteRegistrationProcess($email, $password, $confirmed);

        // Assert
        $this->assertEquals($cookie, $actualCookie);
    }

    /**
     * 測試：當 completeRegistrationProcess 處理資料失敗時
     */
    #[Test]
    public function completeRegistrationProcess_throwsException_onDataInvalid(): void
    {
        $errorMessage = '資料無效';
        $email        = 'test@example.com';
        $password     = 'Test0000';
        $confirmed    = 'Test0000';

        // Arrange mocks
        $this->mockEnsureDataValid(null, $errorMessage);

        // Act
        $this->executeCompleteRegistrationProcess($email, $password, $confirmed, $errorMessage);
    }

    /**
     * 測試：當 completeRegistrationProcess 建立會員資料失敗時
     */
    #[Test]
    public function completeRegistrationProcess_throwsException_onCreateMember(): void
    {
        $errorMessage = '建立會員失敗';
        $email        = 'test@example.com';
        $password     = 'Test0000';
        $confirmed    = 'Test0000';

        // Arrange mocks
        $this->mockEnsureDataValid([
            'email' => $email, 'password' => $password, 'guest' => Mockery::mock(Guest::class)
        ]);
        $this->mockCreateMember(null, $errorMessage);

        // Act
        $this->executeCompleteRegistrationProcess($email, $password, $confirmed, $errorMessage);
    }

    /**
     * 測試：當 completeRegistrationProcess 建立會員資料失敗時
     */
    #[Test]
    public function completeRegistrationProcess_throwsException_onCookie(): void
    {
        $errorMessage = '設定 Cookie 失敗';
        $email        = 'test@example.com';
        $password     = 'Test0000';
        $confirmed    = 'Test0000';

        $userMock = Mockery::mock(User::class)->makePartial();
        $userMock->bearer_token = 'MockBearerToken';

        // Arrange mocks
        $this->mockEnsureDataValid([
            'email' => $email, 'password' => $password, 'guest' => Mockery::mock(Guest::class)
        ]);
        $this->mockCreateMember($userMock);
        $this->mockSetCookie(null, $errorMessage);

        // Act
        $this->executeCompleteRegistrationProcess($email, $password, $confirmed, $errorMessage);
    }

    // ---------------- Helper : initiateRegistrationProcess ----------------

    /**
     * 模擬 dispatchVerification 的行為
     */
    private function mockDispatchVerification(Request $request, ?string $errorMessage = null): void
    {
        $mock = $this->orchestratorMock->shouldReceive('dispatchVerification')
                                       ->once();

        ($errorMessage) ? $mock->andThrow(new Exception($errorMessage))
                        : $mock->with('register', $request);
        
        $this->addToAssertionCount(1);
    }

    /**
     * 執行initiateRegistrationProcess 的測試
     */
    private function executeInitiateRegistrationProcess(Request $request, ?string $errorMessage = null): void
    {
        if ($errorMessage){
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage($errorMessage);
        }

        $this->service->initiateRegistrationProcess($request);
    }

    // ---------------- Helper : authorizeSetPasswordPage ----------------

    /**
     * 模擬 ensureAccountValid 的行為
     */
    private function mockEnsureAccountValid(string $email, ?string $errorMessage = null): void
    {
        $mock = $this->unitServiceMock->shouldReceive('ensureAccountValid')
                                      ->once()
                                      ->with($email);

        ($errorMessage) ? $mock->andThrow(new Exception($errorMessage))
                        : $mock->andReturnNull();

        $this->addToAssertionCount(1);
    }

    /**
     * 模擬 verifyRegisterToken 的行為
     */
    private function mockVerifyRegisterToken(string $email, string $token, ?string $errorMessage = null ): void
    {
        $mock = $this->unitServiceMock->shouldReceive('verifyRegisterToken')
                                      ->once()
                                      ->with($token, $email);
        
        ($errorMessage) ? $mock->andThrow(new Exception($errorMessage))
                        : $mock->andReturnNull();

        $this->addToAssertionCount(1);                              
    }

    /**
     * 執行 authorizeSetPasswordPage 的測試
     */
    private function executeAuthorizeSetPasswordPage(string $email, string $token, ?string $errorMessage = null): void
    {
        if ($errorMessage){
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage($errorMessage);
        }

        $this->service->authorizeSetPasswordPage($email, $token);
    }

    // ---------------- Helper : completeRegistrationProcess ----------------

    /**
     * 模擬 ensureDataValid 的行為
     */
    private function mockEnsureDataValid(?array $data = null, ?string $errorMessage = null): void
    {
        $mock = $this->unitServiceMock->shouldReceive('ensureDataValid')
                                      ->once();

        ($errorMessage) ? $mock->andThrow(new Exception($errorMessage))
                        : $mock->andReturn($data);
    }

    /**
     * 模擬 createMember 的行為
     */
    private function mockCreateMember(?object $userMock, ?string $errorMessage = null): void
    {
        $mock = $this->unitServiceMock->shouldReceive('createMember')
                                      ->once();

        ($errorMessage) ? $mock->andThrow(new Exception($errorMessage))
                        : $mock->andReturn($userMock);                   
    }

    /**
     * 模擬 setCookie 的行為
     */
    private function mockSetCookie(?Cookie $cookie = null, ?string $errorMessage = null): void
    {
        $mock = $this->unitServiceMock->shouldReceive('setCookie')
                                      ->once();

        ($errorMessage) ? $mock->andThrow(new Exception($errorMessage))
                        : $mock->andReturn($cookie);
    }


    /**
     * 執行 completeRegistrationProcess 的測試
     */
    private function executeCompleteRegistrationProcess(
        string $email, 
        string $password, 
        string $confirmed, 
        ?string $errorMessage = null
    ): Cookie
    {
        if ($errorMessage){
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage($errorMessage);
        }

        return $this->service->completeRegistrationProcess($email, $password, $confirmed);
    }
}
