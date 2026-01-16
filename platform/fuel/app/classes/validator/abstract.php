<?php

use Fuel\Core\Validation;

abstract class Validator_Abstract
{
    /** @var Validation */
    protected $instance;

    abstract public function build_validation(): Validation;

    public function validate(array $data): void
    {
        $validation = $this->build_validation();

        if ($validation->run($data) === false) {
            throw new InvalidArgumentException(
                $this->escape_error_message($validation->show_errors())
            );
        }
    }

    private function escape_error_message(string $error): string
    {
        return strip_tags($error);
    }
}
