<?php

namespace Fuel\Tasks;

use Closure;
use Fuel\Core\Cache;
use Fuel\Core\DB;
use Fuel\Tasks\Seeders\Seeder;
use Throwable;

/**
 * Seed task - main seeder.
 */
final class Seed
{

    /**
     * Class names for seeders, they will be called in seed all method.
     */
    const SEEDERS = [
        'Currency',
        'Language',
        'Lottery',
        'Lottery_Source',
        'Lottery_Provider',
        'Lottery_Type',
        'Lottery_Type_Data',
        'Lottery_Group',
        'Mail_Templates',
        'Payment_Method',
        'Setting',
        'Payment_Method_Supported_Currency',
        'Withdrawal',
        'Whitelabel',
        'Whitelabel_Payment_Method',
        'Whitelabel_Payment_Method_Currency',
        'Payment_Method_Currency',
        'Whitelabel_Setting',
        'Whitelabel_Plugin',
        'Whitelabel_Withdrawal',
        'Whitelabel_Aff_Withdrawal',
        'Whitelabel_Default_Currency',
        'Whitelabel_Language',
        'Whitelabel_Lottery',
        'Whitelabel_Aff_Group',
        'Whitelabel_Aff',
//        'Payment_Methods',
        'Bonus',
        'Mail_Template_For_Welcome_Bonus',
        'Zambia_Integration',
        'Whitelabel_User',
        'Flutterwave',
        'Payment_Method_Astro_Pay_Card',
        'Notification_Draw_Email',
        'Mail_Templates_Fixes',
        'Flutterwave_Africa',
        'CreditCardSandbox',
        'TruevoCC',
        'VisaNet',
        'Ggworld_Integration',
        'Peru_Integration',
        'Admin_User_Roles',
        'Custom',
        'Multi_Draws_Email',
        'Ggworld_X_Integration',
        'Ggworld_Million_Integration',
        'Mail_Templates_Fixes_3',
        'Payment_Method_Bhartipay',
        'Referafriend_Bonus',
        'Mail_Template_For_Referafriend',
        'Sepa',
        'Multi_Draws_Email',
        'Multidraw_Notification_Email',
        'Florida_Lotto',
        'Modules',
        'Superadmin_Seeder',
        'Mail_Template_For_Promo_Code_Bonus',
        'Megasena',
        'Quina',
        'OtosLotto',
        'HatosLotto',
        'SetForLifeUK',
        'Thunderball',
        'LottoAmerica',
        'LottoAT',
        'Lotto6Aus49',
//        'Raffle_Closed',
        'Whitelabel_Api',
        'Whitelabel_Api_Ip',
        'Crm_Module_Raffle_Tickets',
//        'Raffle_Lotteryking_Prize_In_Kind',
//        'Raffle_Lotteryking_Closed',
//        'Faireum_Raffle_Closed',
        'Modules_Add_Edit_Bonus_Balance',
        'Faireum_Custom_Withdrawal',
        'Update_Saturday_Lotto',
//        'Raffle_Happy_Together_Vietnam_Closed',
//        'One_Number_Lotto_Raffle_Closed',
        'SkandinavLotto',
        'LottoMultiMulti',
        'Keno_Integration',
        'Keno_Prizes',
        'Jeton',
        'Astro_Pay_One_Touch',
        'Tamspay',
        'Trustpayments',
        'Payments_Config_Fix',
        'PayOp',
        'SlotsSlotegratorProviderData',
        'ModulesAddCasinoTransactionsView',
        'ModulesAddCasinoWithdrawalsView',
        'ModulesAddCasinoWithdrawalsEdit',
        'ModulesAddCasinoDepositsView',
        'ModulesAddDepositsView',
        'ModulesAddCasinoReportsView',
        'WonderlandPay',
        'ModulesAddEditUsersCasinoBalance',
        'Picksell',
        'FaireumCryptoexchangesWithdrawal',
        'PspGate',
        'UpdateEurojackpotDrawDate',
        'UpdateEurojackpotRules',
        'UpdateEurojackpotOddsTable',
        'UpdateOzLottoRules',
        'UpdateUsersEditModuleName',
        'Superadmin_Privileges',
        'UpdateAstroPayOneTouchPaymentMethodName',
        'Mail_Template_For_Support_Ticket',
        'ModulesAddWhitelabelCasinoSettings',
        'TurnOnGGWorldScan',
        'TurnOffGGWorldRaffle',
        'AddMailTemplatesForSocialMediaLogin',
        'Onramper',
        'AddWordpressWhitelistUnfilteredHtmlEditor',
        'NowPayments',
        'MailTemplateReducePadding',
        'AddPrimeadsPlugin',
        'AddFacebookSocialType',
        'AddSocialFacebookTestApiConfig',
        'UpdateGgWorldKenoTicketPrice',
        'Zen',
        'Gcash',
        'ModulesAddSeoWidgets',
        'AddGoogleSocialType',
        'PolishKenoIntegration',
        'GreekKenoIntegration',
        'AddHebrewLanguage',
        'CzechKenoIntegration',
        'SlovakKenoIntegration',
        'LatvianKenoIntegration',
        'FinnishKenoIntegration',
        'FrenchKenoIntegration',
        'EuroDreamsIntegration',
        'HungarianKenoIntegration',
        'ItalianKenoIntegration',
        'WeekdayWindfallIntegration',
        'Lenco',
        'SlovakKeno10Integration',
        'GermanKenoIntegration',
        'UkrainianKenoIntegration',
        'BelgianKenoIntegration',
        'KenoNewYorkIntegration',
        'EuromillionsSuperdrawIntegration',
        'LoteriaRomanaIntegration',
        'MiniPowerballIntegration',
        'BrazilianKenoIntegration',
        'SwedishKenoIntegration',
        // 'AustralianKenoIntegration',
        'DanishKenoIntegration',
        'NorwegianKenoIntegration',
        // 'LithuanianKenoIntegration',
        // 'CroatianKenoIntegration',
        // 'BelarusianKenoIntegration',
        // 'EstonianKenoIntegration',
        // 'CanadianKenoIntegration',
        'MiniMegaMillionsIntegration',
        'MiniEuromillionsIntegration',
        'MiniEurojackpotIntegration',
        'MiniSuperEnalottoIntegration',
        'LatvianKenoChangeRules',
        'UkrainianKenoChangeRules',
        'MegaMillionsChangeRules',
        'MiniMegaMillionsChangeRules',
    ];
    /**
     * Instantiate seeder class.
     * NOTE: this way due to fuel autoloader not working on CLI.
     *
     * @param string $name
     * @return Seeder
     */
    private function instantiateSeeder(string $name)
    {
        $seedersPath = __DIR__ . DIRECTORY_SEPARATOR . 'seeders' . DIRECTORY_SEPARATOR;
        require_once($seedersPath . 'seeder.php');

        $filePath = "{$seedersPath}{$name}.php";
        if (file_exists($filePath)) {
            require_once($filePath);
        } else {
            require_once($seedersPath . strtolower($name) . '.php');
        }

        $fullName = "Fuel\\Tasks\\Seeders\\$name";
        echo "Seed $fullName\r\n";
        return new $fullName();
    }

    /**
     * Execute all seeders.
     * Call with php oil refine seed
     *
     * @return void
     */
    public function run(): void
    {
        $this->execute_in_transaction(function (): void {
            foreach (self::SEEDERS as $seeder_name) {
                $this->instantiateSeeder($seeder_name)->execute();
            }
        });
    }

    /**
     * Execute specific seeder.
     * Call with php oil refine seed:class "Name"
     *
     * @param string $name name of the seeder class.
     *
     * @return void
     */
    public function class(string $name): void
    {
        $this->execute_in_transaction(function () use ($name): void {
            $this->instantiateSeeder($name)->execute();
        });
    }

    /**
     * Execute batch of seeders.
     * Call with php oil refine seed:batch 1 10
     *
     * @param string $start number of starting seeder.
     * @param string $end number of ending seeder.
     *
     * @return void
     */
    public function batch(int $start, int $end): void
    {
        $this->execute_in_transaction(function () use ($start, $end): void {
            for ($index = $start; $index <= $end; $index++) {
                $this->instantiateSeeder(self::SEEDERS[$index])->execute();
            }
        });
    }

    /**
     * Execute logic wrapped in database transaction.
     *
     * @param Closure $logic
     * @return void
     * @throws Throwable exception if any occur during execution.
     */
    private function execute_in_transaction(Closure $logic): void
    {
        try {
            DB::start_transaction();
            $logic();
            DB::commit_transaction();
            Cache::delete_all(); // clear cache, after successful transaction
        } catch (Throwable $throwable) {
            DB::rollback_transaction();
            throw $throwable;
        }
    }
}
