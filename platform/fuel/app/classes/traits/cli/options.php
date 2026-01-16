<?php

use Fuel\Core\Cli;

trait Cli_Options
{
    private $options = [];
    private $cli_option_names = [];

    private function set_acceptable_options($options): void
    {
        $this->cli_option_names = $options;
    }

    public function get_options(): array
    {
        return $this->options;
    }

    public function get_option($key): ?string
    {
        return $this->options[$key] ?? null;
    }

    public function set_options(array $options): void
    {
        $this->options = array_merge($this->options, $options);
    }

    private function is_cli(): bool
    {
        return (php_sapi_name() === 'cli');
    }

    private function read_options(): void
    {
        foreach ($this->cli_option_names as $option_name) {
            $option_value = Cli::option($option_name);
            if ($option_value !== null) {
                $this->options['option_name'] = $option_value;
            }
        }
    }

    private function cli_error($message): void
    {
        if ($this->is_cli()) {
            Cli::error($message);
        }
    }
}
