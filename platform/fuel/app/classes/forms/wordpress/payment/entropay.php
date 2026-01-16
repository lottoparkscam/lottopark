<?php

use Services\Logs\FileLoggerService;

final class Forms_Wordpress_Payment_Entropay implements Forms_Wordpress_Payment_Process
{
    use Traits_Payment_Method;

    /**
     *
     * @return Validation object
     */
    public function get_prepared_form()
    {
        $val = Validation::forge("entropay");
        
        return $val;
    }
    
    /**
     *
     */
    public function process_form()
    {
    }
    
    /**
     * This function was moved here to process everything what is connected
     * with entropay in one place.
     *
     * @return array
     */
    public static function get_banned_countries()
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $filename_start = APPPATH . 'vendor/entropay/banned_countries';
        $filename_CSV = $filename_start . '.csv';
        $filename_JSON = '';

        // Check filemtime to create JSON file based on filemtime
        $time = filemtime($filename_CSV);
        $filename_JSON .= $filename_start . '-' . $time . '.json';

        if (file_exists($filename_JSON)) {
            $banned_countries = json_decode(file_get_contents($filename_JSON), true);
            return $banned_countries;
        }

        $banned_countries = [];
        if (($handle = fopen($filename_CSV, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                $banned_countries[] = $data[1];
            }
            fclose($handle);
        }

        if (file_put_contents($filename_JSON, json_encode($banned_countries, JSON_UNESCAPED_UNICODE)) === false) {
            $error_text = "There is a problem with creation of the banned_countries file: " . $filename_JSON;
            $fileLoggerService->error($error_text);
            // I haven't seen in the code place that this Exception is caught
            throw new Exception($error_text);
        }

        return $banned_countries;
    }

    /**
     *
     * @return void
     */
    public function create_payment(): void
    {
        $this->process_form();
    }
    
    /**
     *
     * @param Model_Whitelabel_Transaction $transaction
     * @param string $out_id
     * @param array $data
     * @return void
     */
    public function confirm_payment(
        Model_Whitelabel_Transaction &$transaction = null,
        string &$out_id = null,
        array &$data = []
    ): bool {
        $ok = false;
        
        return $ok;
    }
}
