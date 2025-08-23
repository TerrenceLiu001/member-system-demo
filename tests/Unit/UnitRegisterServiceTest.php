<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\MemberRegister\UnitRegisterService;
use App\Models\User;
use App\Models\Guest;
use App\Services\MemberAuthService;
use App\Services\MemberEmailService;
use App\Services\MemberEditService;
use App\Services\ValidationService;
use App\Repositories\Tokens\Implementations\EloquentUserRepository;
use App\Repositories\Tokens\Implementations\EloquentGuestRepository;
use Mockery;
use Exception;
use Tests\TestCase;

class UnitRegisterServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UnitRegisterService $service;
    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(MemberAuthService::class);
        $this->mock(MemberEmailService::class);
        $this->mock(MemberEditService::class);
        $this->mock(ValidationService::class);
        $this->mock(EloquentUserRepository::class);
        $this->mock(EloquentGuestRepository::class);

        $this->service = $this->app->make(UnitRegisterService::class);
    }

    // ---------------- ensureAccountValid ----------------

    /**
     * 測試：ensureAccountValid 成功時
     */
    #[Test]
    public function ensureAccountValid_passes_onValidEmailAndNonExistingAccount(): void
    {
        $email = 'test@example.com';

        // Arrange mocks 
        $this->mockValidationEmail($email);
        $this->mockFindAccount($email);

        // Act
        $this->executeEnsureAccountValid($email);
    }

    /**
     * 測試：當 Email 格式無效時，會拋出錯誤
     */
    #[Test]
    public function ensureAccountValid_throwsException_onInvalidEmailFormat(): void
    {
        $errorMessage = '電子信箱格式錯誤';
        $email = 'invalid-email-format';

        // Arrange mocks
        $this->mockValidationEmail($email, $errorMessage);

        // Act
        $this->executeEnsureAccountValid($email, $errorMessage);
    }

    /*
     * 測試：當 Email 是空字串時，確保 ensureAccountValid 會拋出錯誤
     */
    #[Test]
    public function ensureAccountValid_throwsException_onEmptyEmail(): void
    {
        $errorMessage = '請輸入電子信箱';
        $email = '';

        // Arrange mocks
        $this->mockValidationEmail('', $errorMessage);

        // Act
        $this->executeEnsureAccountValid($email, $errorMessage);
    }

    /**
     * 測試：當 Email 已被註冊時，確保 ensureAccountValid 會拋出錯誤
     */
    #[Test]
    public function ensureAccountValid_throwsException_onExistingEmail(): void
    {
        $errorMessage = '此信箱已被註冊，請直接登入';
        $email = 'test@example.com';

        // Arrange mocks
        $this->mockValidationEmail($email);
        $this->mockFindAccount($email, Mockery::mock(User::class));

        // Act
        $this->executeEnsureAccountValid($email, $errorMessage);
    }

    // ---------------- verifyRegisterToken ----------------


    /**
     * 測試：當 Token 有效時，verifyRegisterToken 應成功執行
     */
    #[Test]
    public function verifyRegisterToken_passes_onValidToken(): void
    {
        $token = 'valid-token';
        $email = 'test@example.com';

        $guestMock = Mockery::mock(Guest::class)->makePartial();
        $guestMock->email = $email;

        // Arrange mocks
        $this->mockVerifyRegisterToken($guestMock);

        // Act
        $this->executeVerifyRegisterToken($token, $email);
    }

    /**
     * 測試：當 Token 無效時，verifyRegisterToken 應拋出例外
     */
    #[Test]
    public function verifyRegisterToken_throwsException_onInvalidToken(): void
    {
        $errorMessage = '請重新流程';
        $token = 'invalidToken';
        $email = 'test@example.com';

        // Arrange mocks
        $this->mockVerifyRegisterToken();

        // Act
        $this->executeVerifyRegisterToken($token, $email, $errorMessage);
    }

    /**
     * 測試：當 Token 的 Email 與傳入的不符時，verifyRegisterToken 應拋出例外
     */
    #[Test]
    public function verifyRegisterToken_throwsException_onMismatchedEmail(): void
    {
        $errorMessage = '無效連結，請重新註冊';
        $token = 'registerToken';
        $email = 'test@example.com';

        $guestMock = Mockery::mock(Guest::class)->makePartial();
        $guestMock->email = 'token@example.com';

        // Arrange mocks
        $this->mockVerifyRegisterToken($guestMock);

        // Act
        $this->executeVerifyRegisterToken($token, $email, $errorMessage);
    }

    // ---------------- createMember ----------------


    /**
     * 測試：當所有操作都成功時，createMember 應成功建立會員並回傳 User 物件
     */
    #[Test]
    public function createMember_createsUser_onSuccess(): void
    {
        $data = $this->prepareMockData();
        $userMock = Mockery::mock(User::class)->makePartial();
        $userMock->id = 100;

        // Arrange mocks
        $this->mockMarkStatus();
        $this->mockCreate($userMock);
        $this->mockGenerateToken();
        $this->mockHandleToken();

        // Act
        $result = $this->executeCreateMember($data);
        
        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($userMock->id, $result->id);
    }

    /**
     * 測試：當 Guests::markStatus() 失敗
     */
    #[Test]
    public function createMember_throwsException_onGuestStatusUpdateFailure(): void
    {
        $errorMessage = '更新訪客狀態失敗';
        $data = $this->prepareMockData();
        
        // Arrange mocks
        $this->mockMarkStatus($errorMessage);

        // Act
        $this->executeCreateMember($data, $errorMessage);
    }

    /**
     * 測試：當建立 User 失敗時
     */
    #[Test]
    public function createMember_throwsException_onUserCreationFailure(): void
    {

        $errorMessage = '使用者建立失敗';
        $data = $this->prepareMockData();
        $userMock = Mockery::mock(User::class)->makePartial();
        $userMock->id = 100;

        // Arrange mocks
        $this->mockMarkStatus();
        $this->mockCreate($userMock, $errorMessage);

        // Act
        $this->executeCreateMember($data, $errorMessage);
    }

    /**
     * 測試：當 MemberAuthService::generateToken() 失敗
     */
    #[Test]
    public function createMember_throwsException_onTokenGenerationFailure(): void
    {
        $errorMessage = 'Token 生成失敗';
        $data = $this->prepareMockData();
        $userMock = Mockery::mock(User::class)->makePartial();
        $userMock->id = 100;

        // Arrange mocks
        $this->mockMarkStatus();
        $this->mockCreate($userMock);
        $this->mockGenerateToken($errorMessage);

        // Act
        $this->executeCreateMember($data, $errorMessage);
    }

    /**
     * 測試：當 Users::handleToken() 失敗
     */
    #[Test]
    public function createMember_throwsException_onTokenHandlingFailure(): void
    {

        $errorMessage = 'Token 處理失敗';
        $data = $this->prepareMockData();
        $userMock = Mockery::mock(User::class)->makePartial();
        $userMock->id = 100;
        
        // Arrange mocks
        $this->mockMarkStatus();
        $this->mockCreate($userMock);
        $this->mockGenerateToken();
        $this->mockHandleToken($errorMessage);

        // Act
        $this->executeCreateMember($data, $errorMessage);
    }

    // ---------------- ensureDataValid ----------------

    /**
     * 測試：當所有操作都成功時，ensureDataValid 應成功並回傳資料陣列
     */
    #[Test]
    public function ensureDataValid_passes_onValidData(): void
    {
        $email = 'test@example.com';
        $password = 'Test0000';
        $confirmed = 'Test0000';

        $guestMock = Mockery::mock(Guest::class)->makePartial();
        $guestMock->email = $email;
    
        // Arrange mocks
        $this->mockEnsureAccountValid($email);
        $this->mockFindPendingRecord($guestMock);
        $this->mockVerifyToken($guestMock);
        $this->mockCheckPasswordInputs();
    
        // Act
        $result = $this->executeEnsureDataValid($email, $password, $confirmed);
    
        // Assert
        $this->assertEquals($email, $result['email']);
        $this->assertEquals($password, $result['password']);
        $this->assertInstanceOf(Guest::class, $result['guest']);
        $this->assertEquals($guestMock->email, $result['guest']->email);
    }

    /**
     * 測試：當 ensureAccountValid 失敗時，ensureDataValid 應拋出例外
     */
    #[Test]
    public function ensureDataValid_throwsException_onEnsureAccountValidFailure(): void
    {
        $errorMessage = '此信箱已被註冊，請直接登';

        $email = 'test@example.com';
        $password = 'Test0000';
        $confirmed = 'Test0000';
    
        // Arrange mocks
        $this->mockEnsureAccountValid($email , $errorMessage);

        // Act
        $this->executeEnsureDataValid($email, $password , $confirmed, $errorMessage);
    }

    /**
     * 測試：當 findPendingRecord 失敗時，ensureDataValid 應拋出例外
     */
    #[Test]
    public function ensureDataValid_throwsException_onFindPendingRecordFailure(): void
    {
        $errorMessage = '找不到待處理的訪客紀錄';

        $email = 'test@example.com';
        $password = 'Test0000';
        $confirmed = 'Test0000';

        $guestMock = Mockery::mock(Guest::class)->makePartial();
        $guestMock->email = $email;
    
        // Arrange mocks
        $this->mockEnsureAccountValid($email);
        $this->mockFindPendingRecord($guestMock, $errorMessage);
    
        // Act
        $this->executeEnsureDataValid($email, $password , $confirmed, $errorMessage);
    }

    /**
     * 測試：當 verifyToken 失敗時，ensureDataValid 應拋出例外
     */
    #[Test]
    public function ensureDataValid_throwsException_onVerifyTokenFailure(): void
    {
        $errorMessage = '無效或過期的 Token';

        $email = 'test@example.com';
        $password = 'Test0000';
        $confirmed = 'Test0000';

        $guestMock = Mockery::mock(Guest::class)->makePartial();
        $guestMock->email = $email;

        // Arrange mocks
        $this->mockEnsureAccountValid($email);
        $this->mockFindPendingRecord($guestMock);
        $this->mockVerifyToken($guestMock, $errorMessage);
    
        // Act
        $this->executeEnsureDataValid($email, $password , $confirmed, $errorMessage);
    }

    /**
     * 測試：當 checkPasswordInputs 失敗時，ensureDataValid 應拋出例外
     */
    #[Test]
    public function ensureDataValid_throwsException_onCheckPasswordInputsFailure(): void
    {
        $errorMessage = '密碼與確認密碼不符';
    
        $email = 'test@example.com';
        $password = 'Test0000';
        $confirmed = 'Test0000';

        $guestMock = Mockery::mock(Guest::class)->makePartial();
        $guestMock->email = $email;

        // Arrange mocks
        $this->mockEnsureAccountValid($email);
        $this->mockFindPendingRecord($guestMock);
        $this->mockVerifyToken($guestMock);
        $this->mockCheckPasswordInputs($errorMessage);
    
        // Act
        $this->executeEnsureDataValid($email, $password , $confirmed, $errorMessage);
    }

    // ---------------- Helper : ensureAccountValid ----------------

    /**
     * 模擬 validateEmail
     */
    private function mockValidationEmail(string $email, ?string $errorMessage = null): void
    {
        /** @var ValidationService&\Mockery\MockInterface */
        $validationMock = $this->app->make(ValidationService::class);
        $mock= $validationMock->shouldReceive('validateEmail')
                              ->once()
                              ->with($email);
        
        ($errorMessage) ? $mock->andThrow(new Exception($errorMessage)):
                          $mock->andReturnNull();
    }

    /**
     * 對 UserRepository 設定 findAccount 行為
     */
    private function mockFindAccount(string $email, ?object $returnObject = null): void
    {
        /** @var EloquentUserRepository&\Mockery\MockInterface */
        $userRepoMock = $this->app->make(EloquentUserRepository::class);
        $userRepoMock->shouldReceive('findAccount')
                     ->once()
                     ->with($email)
                     ->andReturn($returnObject);
    }

    /**
     * 執行 ensureAccountValid 的測試
     */
    private function executeEnsureAccountValid(string $email, ?string $errorMessage = null): void
    {
        if ($errorMessage){
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage($errorMessage);
        }

        $this->service->ensureAccountValid($email);
    }

    // ---------------- Helper : verifyRegisterToken ----------------

    /**
     * 模擬 MemberAuthService 的 verifyToken 方法行為
     */
    private function mockVerifyRegisterToken(?object $guestMock = null): void
    {
        /** @var MemberAuthService&\Mockery\MockInterface */
        $memberAuthServiceMock = $this->app->make(MemberAuthService::class);
        $mock = $memberAuthServiceMock->shouldReceive('verifyToken')
                                      ->once();

        ($guestMock) ? $mock->andReturn($guestMock)
                     : $mock->andThrow(new Exception('請重新流程'));
    }

    /**
     * 執行 ensureAccountValid 的測試
     */
    private function executeVerifyRegisterToken(string $token, string $email, ?string $errorMessage = null): void
    {
        if ($errorMessage){
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage($errorMessage);
        }
        $this->service->verifyRegisterToken($token, $email);
    }

    // ---------------- Helper : createMember ----------------

    /**
     * 準備模擬參數
     */
    private function prepareMockData(): array
    {
        return [
            'email'    => 'test@example.com',
            'password' => 'Test0000',
            'guest'    => Mockery::mock(Guest::class)->makePartial()
        ];
    }

    /**
     * 模擬 makeStatus 的行為
     */
    private function mockMarkStatus(?string $errorMessage = null):void
    {
        /** @var EloquentGuestRepository&\Mockery\MockInterface */
        $guestRepoMock = $this->app->make(EloquentGuestRepository::class);
        $mock = $guestRepoMock->shouldReceive('markStatus');
        
        ($errorMessage) ? $mock->andThrow(new Exception($errorMessage))
                        : $mock->andReturnNull();
    }

    /**
     * 模擬 create 的行為
     */
    private function mockCreate(?object $userMock, ?string $errorMessage = null): void
    {
        /** @var EloquentUserRepository&\Mockery\MockInterface */
        $userRepoMock = $this->app->make(EloquentUserRepository::class);
        $mock = $userRepoMock->shouldReceive('create');

        ($errorMessage) ? $mock->andThrow(new Exception($errorMessage)) 
                        : $mock->andReturn($userMock);
    }

    /**
     * 模擬 generateToken 的行為
     */
    private function mockGenerateToken(?string $errorMessage = null): void
    {
        /** @var MemberAuthService&\Mockery\MockInterface */
        $memberAuthServiceMock = $this->app->make(MemberAuthService::class);
        $mock = $memberAuthServiceMock->shouldReceive('generateToken');

        ($errorMessage) ? $mock->andThrow(new Exception($errorMessage)) 
                        : $mock->andReturn('bearer_token');
    }

    /**
     * 模擬 handleToken 的行為
     */
    private function mockHandleToken(?string $errorMessage = null): void
    {
        /** @var EloquentUserRepository&\Mockery\MockInterface */
        $userRepoMock = $this->app->make(EloquentUserRepository::class);
        $mock = $userRepoMock->shouldReceive('handleToken');
        
        ($errorMessage) ? $mock->andThrow(new Exception($errorMessage)):
                          $mock->andReturnNull();
    }

    /**
     * 執行 createMember 的測試
     */
    private function executeCreateMember(array $data, ?string $errorMessage = null): object
    {
        if ($errorMessage){
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage($errorMessage);
        }

        return $this->service->createMember($data);
    }

    // ---------------- Helper : ensureDataValid ----------------

    /**
     * 模擬 ensureAccountValid 的行為
     */
    private function mockEnsureAccountValid(string $email, ?string $errorMessage = null): void
    {
        if ($errorMessage) {
            $this->mockValidationEmail($email);
            $this->mockFindAccount($email, Mockery::mock(User::class));

        } else {
            $this->mockValidationEmail($email);
            $this->mockFindAccount($email);
        }
    }

    /**
     * 模擬 findPendingRecord 的行為
     */
    private function mockFindPendingRecord(object $guestMock,  ?string $errorMessage = null): void
    {
        /** @var EloquentGuestRepository&\Mockery\MockInterface */
        $guestRepoMock = $this->app->make(EloquentGuestRepository::class);
        $mock = $guestRepoMock->shouldReceive('findPendingRecord')
                              ->once();

        ($errorMessage) ? $mock->andThrow(new Exception($errorMessage))
                        : $mock->andReturn($guestMock);
    }

    /**
     * 模擬 verifyToken 的行為
     */
    private function mockVerifyToken(object $guestMock, ?string $errorMessage = null): void
    {
        /** @var MemberAuthService&\Mockery\MockInterface */
        $memberAuthServiceMock = $this->app->make(MemberAuthService::class);
        $mock = $memberAuthServiceMock->shouldReceive('verifyToken')
                                      ->once();

        ($errorMessage) ? $mock->andThrow(new Exception($errorMessage))
                        : $mock->andReturn($guestMock);
    }
    
    /**
     * 模擬 checkPasswordInputs 的行為
     */
    private function mockCheckPasswordInputs(?string $errorMessage = null): void
    {
        /** @var ValidationService&\Mockery\MockInterface */
        $validationServiceMock = $this->app->make(ValidationService::class);
        $mock = $validationServiceMock->shouldReceive('checkPasswordInputs')
                                      ->once();
    
        ($errorMessage)? $mock->andThrow(new Exception($errorMessage))
                       : $mock->andReturnNull(); 
    }

    /**
     * 執行 ensureDataValid 的測試
     */
    private function executeEnsureDataValid(string $email, string $password, string $confirmed, ?string $errorMessage = null): array
    {
        if ($errorMessage){
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage($errorMessage);
        }

        return $this->service->ensureDataValid($email, $password , $confirmed);
    }
}
