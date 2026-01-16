<?php

use Services\Logs\FileLoggerService;

abstract class Events_Event
{
    use Services_Api_Customer;
    use Services_Api_Mautic;

    /**
     * Default function triggered by event
     *
     * @param array $data
     * return void
     */
    public static function handle(array $data): void
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        if (self::isCasinoEventOff($data, IS_CASINO)) {
            return;
        }

        $validated = self::validate($data);
        if ($validated === false) {
            $fileLoggerService->error(
                'Event data validation fail.' . json_encode($data)
            );
            return;
        }
        static::run($data);
    }

    /**
     * Validate Event data
     *
     * @param array $data
     * @return bool
     */
    protected static function validate(array $data): bool
    {
        return !(!isset($data['user_id']) || !isset($data['whitelabel_id']) || !isset($data['plugin_data']));
    }

    /**
     * Run event
     *
     * @param array $data
     * return void
     */
    protected static function run(array $data): void
    {
        if (self::isCasinoEventOff($data, IS_CASINO)) {
            return;
        }

        $data = static::add_custom_data_to_plugins($data);
        self::process_plugins($data);
    }

    public static function isCasinoEventOff(array $data, bool $isCasino): bool
    {
        if ($isCasino) {
            if (isset($data['onCasinoEvent'])) {
                return !$data['onCasinoEvent'];
            }
            return true;
        }
        return false;
    }

    /**
     * Update plugins
     *
     * @param array $data
     * return void
     */
    protected static function process_plugins(array $data): void
    {
        // Update customer-api
        self::process_customer($data['user_id'], $data['whitelabel_id'], $data['plugin_data']);
        // Update mautic-api
        self::process_mautic($data['user_id'], $data['whitelabel_id'], $data['plugin_data']);
    }

    /**
     * Add custom data to plugins.
     *
     * @param array $data
     * return array
     */
    protected static function add_custom_data_to_plugins(array $data): array
    {
        return $data;
    }
}
