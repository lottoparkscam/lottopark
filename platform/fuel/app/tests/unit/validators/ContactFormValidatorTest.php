<?php

namespace Tests\Unit\Validators;

use Services\ContactService;
use Test_Unit;
use Validators\ContactFormValidator;

final class ContactFormValidatorTest extends Test_Unit
{
    private const DUMMY_REQUEST = [
        ContactService::INPUT_NAMES['name'] => 'asd',
        ContactService::INPUT_NAMES['email'] => 'asd@asd.com',
        ContactService::INPUT_NAMES['subject'] => 'asd',
        ContactService::INPUT_NAMES['message'] => 'asd',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->resetInput();
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    /** @test */
    public function isInvalid_missingCaptcha(): void
    {
        $expectedError = [
            'captcha' => 'Wrong captcha.'
        ];

        $this->setInput('POST', self::DUMMY_REQUEST, true);

        $validator = new ContactFormValidator();
        $this->assertFalse($validator->isValid());
        $this->assertSame($expectedError, $validator->getErrors());
    }

    /** @test */
    public function isValid(): void
    {
        $this->setInput('POST', self::DUMMY_REQUEST, true);

        $validator = new ContactFormValidator();
        $validator->checkCaptcha = false;
        $this->assertTrue($validator->isValid());
    }
}
