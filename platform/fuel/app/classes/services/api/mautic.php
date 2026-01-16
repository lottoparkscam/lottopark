<?php

use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;
use Repositories\WhitelabelPluginUserRepository;
use Orm\RecordNotFound;
use Carbon\Carbon;

trait Services_Api_Mautic
{

    /**
     * Check if mautic-api is enabled and update data
     *
     * @param int $user_id
     * @param int $whitelabel_id
     * @param array $data
     * @param string $context
     * return void
     */
    public static function process_mautic(int $user_id, int $whitelabel_id, array $data, string $context = "update_mautic"): void
    {
        $mautic_api_plugin = Model_Whitelabel_Plugin::get_plugin_by_name($whitelabel_id, 'mautic-api');
        if (isset($mautic_api_plugin['is_enabled']) && $mautic_api_plugin['is_enabled'] == true) {
            self::{$context}($mautic_api_plugin, $user_id, self::prepare_data_mautic($data));
        }
    }

    public function getMauticTransactionDeposit(
        bool $userFirstDeposit,
        float $transactionAmountManger,
        string $transactionStatus,
        bool $transactionIsCasino,
        ?float $userTotalDepositManager,
        ?string $transactionPaymentMethodName = null,
    ): array {

        if ($userFirstDeposit) {
            $pluginData['first_deposit_casino'] = $transactionIsCasino;
            $pluginData['first_deposit_date'] = Carbon::now();
            $pluginData['first_deposit_amount_manager'] = $transactionAmountManger;
            $pluginData['first_deposit_method_manager'] = $transactionPaymentMethodName;
            $pluginData['first_deposit_status_manager'] = $transactionStatus;
        }

        $pluginData['last_deposit_casino'] = $transactionIsCasino;
        $pluginData['last_deposit_date'] = Carbon::now();
        $pluginData['last_deposit_amount_manager'] = $transactionAmountManger;
        $pluginData['last_deposit_method_manager'] = $transactionPaymentMethodName;
        $pluginData['last_deposit_status_manager'] = $transactionStatus;

        $pluginData['total_deposit_manager'] = $userTotalDepositManager;

        return $pluginData;
    }

    /**
     * Prepare plugin data to match mautic-api requirements
     *
     * @param array $data
     * @return array
     */
    public static function prepare_data_mautic(array $data): array
    {
        $data['last_update'] = time();

        if(isset($data['gender'])){
            $data['gender'] = $data['gender'] == 1 ? "male" : "female";
        }

        $data = self::format_date_of_array_values($data, [
            'last_balance_update', 'last_deposit_date', 'date_register',
            'created_at', 'last_active', 'last_purchase_date', 'last_update'
        ]);
        $data = self::rename_array_keys($data, [
            'name' => 'firstname',
            'surname' => 'lastname',
            'birthdate' => 'date_of_birth',
            'phone_country' => 'phone_country_code',
            'country' => 'country_code',
            'state' => 'region',
            'zip' => 'zipcode',
            'first_deposit_amount_manager' => 'first_deposit_amount',
            'first_deposit_method_manager' => 'first_deposit_method',
            'first_deposit_status_manager' => 'first_deposit_status',
            'last_deposit_amount_manager' => 'last_deposit_amount',
            'last_deposit_method_manager' => 'last_deposit_method',
            'last_deposit_status_manager' => 'last_deposit_status',
            'total_deposit_manager' => 'total_deposit',
            'total_withdrawal_manager' => 'total_withdrawal',
            'total_purchases_manager' => 'total_purchases',
            'total_net_income_manager' => 'total_net_income',
            'net_winnings_manager' => 'net_winnings',
            'pnl_manager' => 'pnl'
        ]);
        return $data;
    }

    /**
     * Rename keys of array
     *
     * @param array $data
     * @param array $keys_to_update
     * @return array
     */
    public static function rename_array_keys(array $data, array $keys_to_update): array
    {
        foreach($keys_to_update as $old_name => $new_name){
            if(isset($data[$old_name])){
                $data[$new_name] = $data[$old_name];
                unset($data[$old_name]);
            }
        }
        return $data;
    }

    /**
     * Format date in given array values
     *
     * @param array $data
     * @param array $keys
     * @return array
     */
    public static function format_date_of_array_values(array $data, array $keys): array
    {
        foreach($keys as $key){
            if(isset($data[$key])){
                if(!is_numeric($data[$key])){
                    $data[$key] = strtotime($data[$key]);
                }
                $data[$key] = date('Y-m-d H:i:s', $data[$key]);
            }
        }
        return $data;
    }

    public static function request_mautic($context, $whitelabel_plugin)
    {
        $api = new MauticApi();
        $request = $api->newApi(
            $context,
            self::build_auth_mautic($whitelabel_plugin['options']->user, $whitelabel_plugin['options']->password),
            $whitelabel_plugin['options']->url
        );
        return $request;
    }

    public static function build_auth_mautic($user, $password)
    {
        try {
            $settings = [
                'userName' => $user,
                'password' => $password
            ];
            $initAuth = new ApiAuth();
            $auth = $initAuth->newAuth($settings, 'BasicAuth');
            return $auth;
        } catch (\Exception $e) {
            Model_Whitelabel_Plugin_Log::add_log(
                Helpers_General::TYPE_ERROR,
                null,
                "Problem while building auth Customer API: {$e->getMessage()}"
            );
            return false;
        }
    }

    public static function add_mautic(array $whitelabel_plugin, int $user_id, array $data): void
    {
        try {
            $data['test_user'] = Helpers_General::is_development_env();

            $contactApi = self::request_mautic('contacts', $whitelabel_plugin);

            if (!$contactApi) {
                return;
            }

            $response = $contactApi->create($data);

            if (isset($response['errors'][0]['message'])) {
                Model_Whitelabel_Plugin_Log::add_log(
                    Helpers_General::TYPE_ERROR,
                    $whitelabel_plugin['id'] ?? null,
                    "Problem while adding mautic user (whitelabel user ID: $user_id). Response: {$response['errors'][0]['message']}"
                );
                return;
            }

            $contact = $response[$contactApi->itemName()];

            if (!isset($contact['id'])) {
                return;
            }

            $contactApi->removeDnc($contact['id']);

            /** @var mixed $whitelabel_plugin_user */
            $whitelabel_plugin_user = Model_Whitelabel_Plugin_User::forge();
            $whitelabel_plugin_user->whitelabel_user_id = $user_id;
            $whitelabel_plugin_user->whitelabel_plugin_id = $whitelabel_plugin['id'];
            $whitelabel_plugin_user->data = json_encode(['mautic_id' => $contact['id']]);
            $whitelabel_plugin_user->created_at = Carbon::now();
            $whitelabel_plugin_user->save();

        } catch (Exception $exception) {
            Model_Whitelabel_Plugin_Log::add_log(
                Helpers_General::TYPE_ERROR,
                $whitelabel_plugin['id'] ?? null,
                "Problem while adding mautic user (whitelabel user ID: $user_id): {$exception->getMessage()}"
            );
            return;
        }
    }

    public static function update_mautic(array $whitelabel_plugin, int $user_id, array $data): void
    {
        try {
            $pluginRepository = Container::get(WhitelabelPluginUserRepository::class);
            $whitelabelPluginUser = $pluginRepository->findOneByWhitelabelUserAndPluginId($user_id, $whitelabel_plugin['id']);
        } catch (RecordNotFound) {
            self::add_mautic($whitelabel_plugin,$user_id, $data);
            return;
        }

        if (!$whitelabelPluginUser->isActive) {
            return;
        }

        try {
            $whitelabelPluginUserData = json_decode($whitelabelPluginUser->data);

            if (empty($whitelabelPluginUserData->mautic_id)) {
                self::add_mautic($whitelabel_plugin,$user_id, $data);
                return;
            }

            $mauticId = $whitelabelPluginUserData->mautic_id;
            $contactApi = self::request_mautic('contacts', $whitelabel_plugin);

            if (!$contactApi) {
                self::add_mautic($whitelabel_plugin,$user_id, $data);
                return;
            }

            $response = $contactApi->edit($mauticId, $data);

            if (isset($response['errors'][0]['message'])) {
                $errorMessage = $response['errors'][0]['message'];

                if (str_contains($errorMessage, 'Item was not found.')) {
                    $pluginRepository->setAsInactive($whitelabelPluginUser->id);
                    $errorMessage .= ' (user set as inactive)';
                }

                Model_Whitelabel_Plugin_Log::add_log(
                    Helpers_General::TYPE_ERROR,
                    $whitelabel_plugin['id'] ?? null,
                    "Problem while updating mautic user (whitelabel user ID: $user_id). Response: {$errorMessage}"
                );
                return;
            }

            $pluginRepository->updateTimestamp($whitelabelPluginUser->id);

        } catch (Exception $exception) {
            Model_Whitelabel_Plugin_Log::add_log(
                Helpers_General::TYPE_ERROR,
                $whitelabel_plugin['id'] ?? null,
                "Problem while updating mautic user (whitelabel user ID: $user_id): {$exception->getMessage()} {$exception->getFile()}:L{$exception->getLine()}"
            );
        }
    }
}
