<?php

namespace Tests\Unit\Classes\Services;

use Email\Email_Driver;
use Exception;
use Fuel\Core\Package;
use Services\MailerService;
use Test_Unit;
use Services\Logs\FileLoggerService;

class MailerTest extends Test_Unit
{
    private FileLoggerService $fileLoggerService;
    private Email_Driver $mailer;
    private MailerService $mailerService;

    // Given
    private string $to = 'noreply@whitelotto.loc';
    private string $title = 'Some title';
    private string $body = 'Some body';
    private array $from = ['email' => 'noreply@whitelotto.loc', 'name' => 'WhiteLotto'];

    public function setUp(): void
    {
        parent::setUp();
        Package::load('email');
        $this->fileLoggerService = $this->createMock(FileLoggerService::class);
        $this->mailer = $this->createMock(Email_Driver::class);
        $this->mailerService = new MailerService($this->fileLoggerService, $this->mailer);
    }

    /** @test */
    public function sendWithFrom__PassParametersToDriver(): void
    {
        // When
        $this->mailer
            ->expects($this->once())
            ->method('from')
            ->with($this->from['email'], $this->from['name']);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->willReturn(true);

        // Invoke
        $isSuccess = $this->mailerService->send($this->to, $this->title, $this->body, $this->from);

        // Then
        $this->assertTrue($isSuccess);
    }

    /** @test */
    public function sendWithoutFrom__PassDefaultParametersToDriver(): void
    {
        // When
        $this->mailer
            ->expects($this->once())
            ->method('from')
            ->with('noreply@whitelotto.loc', 'WhiteLotto');

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->willReturn(true);

        // Invoke
        $isSuccess = $this->mailerService->send($this->to, $this->title, $this->body);

        // Then
        $this->assertTrue($isSuccess);
    }

    /** @test */
    public function send__PassRestParametersToDriver(): void
    {
        // When
        $this->mailer
            ->expects($this->once())
            ->method('to')
            ->with($this->to);

        $this->mailer
            ->expects($this->once())
            ->method('subject')
            ->with($this->title);

        $this->mailer
            ->expects($this->once())
            ->method('body')
            ->with($this->body);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->willReturn(true);

        // Invoke
        $isSuccess = $this->mailerService->send($this->to, $this->title, $this->body);

        // Then
        $this->assertTrue($isSuccess);
    }

    /** @test */
    public function send_Correctly_ReturnTrue(): void
    {
        // When
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->willReturn(true);

        // Invoke
        $isSuccess = $this->mailerService->send($this->to, $this->title, $this->body);

        // Then
        $this->assertTrue($isSuccess);
    }

    /** @test */
    public function send_Incorrectly_ReturnFalse(): void
    {
        // When
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->willReturn(false);

        // Invoke
        $isSuccess = $this->mailerService->send($this->to, $this->title, $this->body);

        // Then
        $this->assertFalse($isSuccess);
    }

    /** @test */
    public function send_Exception_ReturnFalse(): void
    {
        // When
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->willThrowException(new Exception());

        $this->fileLoggerService
            ->expects($this->once())
            ->method('error');

        // Invoke
        $isSuccess = $this->mailerService->send($this->to, $this->title, $this->body);

        // Then
        $this->assertFalse($isSuccess);
    }

    /** @test */
    public function send__PassBodyInHtmlForm(): void
    {
        $this->mailer
            ->expects($this->once())
            ->method('html_body')
            ->with($this->body);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $isSuccess = $this->mailerService->send($this->to, $this->title, $this->body, [], true);

        $this->assertTrue($isSuccess);
    }
}
