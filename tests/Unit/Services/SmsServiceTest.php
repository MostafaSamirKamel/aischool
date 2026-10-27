<?php

namespace Tests\Unit\Services;

use App\Services\SmsService;
use App\Services\SmsProviders\LogProvider;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SmsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SmsService $smsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->smsService = new SmsService('log');
    }

    /** @test */
    public function it_can_send_sms_message(): void
    {
        $result = $this->smsService->send('+201234567890', 'Test message');

        $this->assertTrue($result);
    }

    /** @test */
    public function it_normalizes_phone_numbers(): void
    {
        $result = $this->smsService->send('01234567890', 'Test message');

        $this->assertTrue($result);
    }

    /** @test */
    public function it_respects_rate_limiting(): void
    {
        config(['sms.rate_limit.enabled' => true]);
        config(['sms.rate_limit.max_per_minute' => 2]);

        $phone = '+201234567890';

        // First two should succeed
        $this->assertTrue($this->smsService->send($phone, 'Message 1'));
        $this->assertTrue($this->smsService->send($phone, 'Message 2'));

        // Third should fail due to rate limit
        $this->assertFalse($this->smsService->send($phone, 'Message 3'));
    }

    /** @test */
    public function it_can_send_template_messages(): void
    {
        config(['sms.templates.welcome' => 'مرحبا {name} في {school_name}']);
        config(['sms.school_name' => 'مدرسة الاختبار']);

        $result = $this->smsService->sendTemplate(
            '+201234567890',
            'welcome',
            ['name' => 'أحمد']
        );

        $this->assertTrue($result);
    }

    /** @test */
    public function it_logs_messages_to_database(): void
    {
        config(['sms.log_messages' => true]);

        $this->smsService->send('+201234567890', 'Test message');

        $this->assertDatabaseHas('sms_logs', [
            'phone' => '+201234567890',
            'message' => 'Test message',
            'status' => 'sent',
        ]);
    }

    /** @test */
    public function it_can_change_provider_at_runtime(): void
    {
        $this->smsService->setProvider('log');

        $this->assertEquals('Log', $this->smsService->getProviderName());
    }

    /** @test */
    public function it_returns_statistics(): void
    {
        config(['sms.log_messages' => true]);

        $this->smsService->send('+201234567890', 'Test 1');
        $this->smsService->send('+201234567891', 'Test 2');

        $stats = $this->smsService->getStats();

        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(2, $stats['sent']);
        $this->assertEquals(0, $stats['failed']);
    }
}
