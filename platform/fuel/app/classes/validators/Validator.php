<?php

namespace Validators;

use Exception;
use Fuel\Core\Input;
use Fuel\Core\Security;
use Fuel\Core\Validation as Validation;
use Helpers\CaptchaHelper;
use Helpers\TypeHelper;
use Lotto_Helper;
use Validators\Rules\Rule;

abstract class Validator
{
    const POST = 'POST';
    const PATCH = 'PATCH';
    const GET = 'GET';
    const JSON = 'JSON';

    private int $line = __LINE__;

    private string $file = __FILE__;

    private Validation $validation;

    private array $errors;

    private array $casts = [];

    protected static string $method = self::POST;

    protected ?array $input = null;

    private array $buildArguments = [];

    private array $extraCheckArguments = [];
    protected bool $isForm = false;
    public bool $checkCaptcha = true;
    protected bool $isCsrfEnabled = true;

    public function __construct()
    {
        $file = $this->file;
        $line = $this->line;
        $randomFieldset = uniqid("{$file}_{$line}");
        $this->validation = Validation::forge($randomFieldset);

        switch ($this::$method) {
            case self::PATCH:
                $this->input = Input::patch();
                break;
            case self::POST:
                $this->input = Input::post();
                break;
            case self::GET:
                $this->input = Input::get();
                break;
            case self::JSON:
                $this->input = Input::json();
                break;
        }
    }

    abstract protected function buildValidation(...$args): void;

    protected function addFieldRule(Rule $rule): void
    {
        $rule->setValidation($this->validation);
        $rule->applyRules();
        $this->casts[$rule->getName()] = $rule->getType();
    }

    /**
     * @param Rule[] $rules
     */
    protected function addFieldRules(array $rules): void
    {
        foreach ($rules as $rule) {
            $this->addFieldRule($rule);
        }
    }

    protected function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    protected function extraChecks(...$args): bool
    {
        return true;
    }

    public function setBuildArguments(...$args): void
    {
        $this->buildArguments = $args;
    }

    public function setExtraCheckArguments(...$args): void
    {
        $this->extraCheckArguments = $args;
    }

    /** @coverage in platform/fuel/app/tests/unit/validators/ContactFormValidatorTest.php */
    public function checkSecurity(): bool
    {
        if ($this->isCsrfEnabled && !Security::check_token()) {
            $this->errors = ['csrf' => _('Security error! Please try again.')];
            return false;
        }

        if (!$this->checkCaptcha) {
            return true;
        }

        if (!CaptchaHelper::checkCaptcha()) {
            $this->errors = ['captcha' => _('Wrong captcha.')];
            return false;
        }

        return true;
    }

    public function isValid(): bool
    {
        $this->buildValidation(...$this->buildArguments);

        if ($this->isForm && !$this->checkSecurity()) {
            return false;
        }

        $isMainValidationNotValid = !$this->validation->run($this->input);
        if ($isMainValidationNotValid) {
            $this->errors = Lotto_Helper::generate_errors($this->validation->error());
            return false;
        }

        return $this->extraChecks(...$this->extraCheckArguments);
    }

    public function isNotValid(): bool
    {
        return !$this->isValid();
    }

    public function setCustomInput(array $input = null): void
    {
        $this->input = array_merge($this->input ?? [], $input);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param string $propertyName
     * @throws Exception
     * 
     * @return mixed properties can store all php types
     */
    public function getProperty(string $propertyName)
    {
        $propertyNotExists = !(key_exists($propertyName, $this->casts));
        if ($propertyNotExists) {
            return '';
        }

        $type = $this->casts[$propertyName];
        $property = $this->validation->validated($propertyName);

        return TypeHelper::cast($property, $type);
    }

    /**
     * @param string[] $properties
     * @return array
     * @throws Exception
     */
    public function getProperties(array $properties): array
    {
        $values = [];

        foreach ($properties as $property) {
            $values[] = $this->getProperty($property);
        }

        return $values;
    }

    /** Alias for getProperties */
    public function getValidatedProperties(array $properties): array
    {
        return $this->getProperties($properties);
    }
}
