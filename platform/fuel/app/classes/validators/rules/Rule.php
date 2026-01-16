<?php

namespace Validators\Rules;

use Container;
use Fuel\Core\Fieldset_Field;
use Fuel\Core\Validation;
use Helpers\CaseHelper;
use Helpers\StringHelper;
use LogicException;
use Services\Logs\FileLoggerService;

abstract class Rule
{
    protected string $name;

    protected string $label;

    protected Validation $validation;

    protected Fieldset_Field $field;
    private FileLoggerService $fileLoggerService;

    /**
     * @throws LogicException when rule class has not been configured with correct type property
     */
    public function __construct(string $name, string $label)
    {
        $this->name = $name;
        $this->label = $label;
        $this->fileLoggerService = Container::get(FileLoggerService::class);

        if (empty($this->type)) {
            throw new LogicException('Rule instance should contain property $type');
        }
    }

    private function setUpCustomRules(): void
    {
        $this->validation->add_callable('\Validators\CustomRules\IsUniqueInDb');
    }

    public function setValidation(Validation $validation): void
    {
        $this->validation = $validation;
        $this->setUpCustomRules();
        $this->field = $this->validation->add($this->name, $this->label);
    }

    abstract public function applyRules(): void;

    /**
     * Important! This method generates silent error when used in Validator in combination with Validator->addFieldRule
     * Rule might not be applied IF this function is executed before firstly calling addFieldRule
     * and can cause unintended user data to not be validated at all
     * @throws LogicException that should not be caught. It should break code until validator is fixed.
     */
    public function addRule(...$args): self
    {
        if (!isset($this->field)) {
            $this->fileLoggerService->error(
                'The rule has not been applied (probably called in Validator) and will accept unvalidated parameters! Fix immediately!'
            );
            throw new LogicException('Rule has not been applied, wrong use of rule');
        }

        $this->field->add_rule(...$args);
        return $this;
    }

    public function setErrorMessage(...$args): self
    {
        if (isset($this->field)) {
            $this->field->set_error_message(...$args);
        }
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $name rule name in "snake_case" format
     * @param string $label rule label in "Title Case" format
     * If parameters are not passed, the names will be generated from class name
     */
    public static function build(string $name = '', string $label = ''): Rule
    {
        // Get child class that invoked build method
        $namespaceAndClassname = static::class;

        if (empty($name)) {
            $name = CaseHelper::camelToSnake(StringHelper::classnameMinusNamespace($namespaceAndClassname));
        }

        // Label has not been passed, but we might have default or generate from name
        if (empty($label)) {
                $label = CaseHelper::snakeToTitle($name);
        }
        return new $namespaceAndClassname($name, $label);
    }
}
