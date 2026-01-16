<?php

/**
 * Base class of responses, child classes should define property-read documentation for fields and specific getters.
 * NOTE: I decided that fuel Response is insufficient.
 */
abstract class Response_Base implements Response_Interface
{
    /**
     * @var array
     */
    protected $attributes;

    /**
     * curl status code for response.
     * @var int
     */
    private $status_code;

    /**
     * @var string|null
     */
    private $error_message;

    /**
     * string message received from outside source.
     * @var string
     */
    private $response_raw;

    /**
     * Fuel validation rules e.g. ['next_draw_date' => ['trim', ['min_length', 3]]]
     *
     * @return array
     */
    protected function define_validation_rules(): array
    {
        return [];
    }

    /**
     * Creates child defined fields.
     * NOTE: call only after successful validation.
     *
     * @return void
     */
    public function define_additional_fields(...$args): void
    {

    }

    /**
     * @var string
     */
    protected $validator_class;

    /**
     * @var int
     */
    const SUCCESS_CODE = 200; // can be overridden by child classes. 

    final public function __construct(array $attributes, string $response_raw)
    {
        $this->status_code = Services_Curl::get_last_request_result_code();
        $this->response_raw = $response_raw;
        $this->attributes = $attributes;
    }

    public static function build(array $attributes, string $response_raw): self
    {
        return new static($attributes, $response_raw);
    }

    public static function build_from_json(string $response): self
    {
        return self::build(json_decode($response, true) ?: [], $response);
    }

    public function get_validation()
    {
        /** @var Validator_Abstract $validator */
        $validator = new $this->validator_class;
        return $validator->build_validation();
    }

    private function is_structure_invalid(): bool
    {
        $validation_rules = $this->define_validation_rules();
        $validation = $this->get_validation();

        foreach ($validation_rules as $name => $rules) {
            $validation->add($name, $name, [], $rules);
        }

        $is_structure_invalid = !$validation->run($this->attributes);
        if ($is_structure_invalid) {
            $this->error_message = $validation->error_message()[0]; // first error will be sufficient.
            return true;
        }

        return false;
    }

    /**
     * Check if response is valid (with expected status code and in expected form)
     * NOTE: if response is invalid then error_message will be set and you can retrieve it by get_error_message()
     * NOTE: this function also will build child defined fields on success.
     * 
     * @return boolean true if valid
     */
    public function is_valid(): bool
    {
        if ($this->get_status_code() !== static::SUCCESS_CODE) {
            $this->error_message = "Status code = {$this->get_status_code()} doesn't match expected " . static::SUCCESS_CODE;
            return false;
        }

        if ($this->is_structure_invalid()) {
            return false;
        }

        return true;
    }

    /**
     * Check if response is invalid (with unexpected status code or in unexpected form)
     * NOTE: if invalid error_message will be set, you can retrieve it by get_error_message()
     *
     * @return boolean
     */
    public function is_invalid(): bool
    {
        return !$this->is_valid();
    }

    public function get_status_code(): int
    {
        return $this->status_code;
    }

    public function get_error_message(): ?string
    {
        return $this->error_message;
    }

    /**
     * Get error message with prepend human readable message and append raw response.
     *
     * @return string
     */
    public function get_full_error_message(): string
    {
        return 'Invalid response: message=' . $this->get_error_message() . "\r\nresponse_raw=$this";
    }

    /**
     * Get property of response.
     *
     * @param string $name
     * @return mixed|null value or null if not found.
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __toString(): string
    {
        return $this->response_raw;
    }
}
