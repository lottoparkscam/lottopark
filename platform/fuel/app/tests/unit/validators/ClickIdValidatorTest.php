<?php

namespace Tests\Unit\Validators\Rules;

use Test_Unit;
use Validators\ClickIdValidator;

class ClickIdValidatorTest extends Test_Unit
{
    /** @test */
    public function isValid_CorrectData(): void
    {
        $this->setInput('GET', ['clickID' => 'saSd78sa22']);
        $clickIdValidator = new ClickIdValidator();
        $isValid = $clickIdValidator->isValid();
        $this->assertTrue($isValid);
    }

    /** @test */
    public function isValid_IncorrectData_WithSpecialChar(): void
    {
        $this->setInput('GET', ['clickID' => 'sasd*78sa*)()22']);
        $clickIdValidator = new ClickIdValidator();
        $isValid = $clickIdValidator->isValid();
        $this->assertFalse($isValid);
    }

    /** @test */
    public function isValid_IncorrectData_WithNull(): void
    {
        $this->setInput('GET', ['clickID' => '']);
        $clickIdValidator = new ClickIdValidator();
        $isValid = $clickIdValidator->isValid();
        $this->assertFalse($isValid);
    }

    /** @test */
    public function setCustomInput_CorrectData(): void
    {
        $input = ['clickID' => 'asdwe109283'];
        $clickIdValidator = new ClickIdValidator();
        $clickIdValidator->setCustomInput($input);
        $isValid = $clickIdValidator->isValid();
        $this->assertTrue($isValid);
    }
}
