<?php

use Fuel\Core\DB;
use Fuel\Core\Input;
use Models\WhitelabelPaymentMethod;
use Services\Logs\FileLoggerService;

/**
 * @deprecated
 */
class Controller_Dev extends Controller
{
    private $addresses_permitted = [
        '127.0.0.1',
        '31.7.56.178',
        '2a02:29b8:dc01:1535::',
        '51.77.244.72',
        '87.98.151.198',
        '116.203.79.154',
        '5.75.210.12',
        '194.182.189.181',
        '141.95.162.98',
        '5.161.200.222'
    ];

    public function before()
    {
        if (!Lotto_Helper::allow_access("empire")) {
            $error = Request::forge('index/404')->execute();
            echo $error;
            exit();
        }

        if (!in_array(Lotto_Security::get_IP(), $this-> addresses_permitted) && \Fuel::$env !== \Fuel::DEVELOPMENT) {
            exit("You are not allowed to access this script.");
        }
        if (array_key_exists('password', Input::post())) {
            $password = $_ENV['DEV_TASK_PASSWORD'] ?? null;
            if ($password === null) {
                echo View::forge('dev/index', ['error' => 'Password not defined. Access blocked']);
                exit();
            }
            if (Input::post()['password'] !== $password) {
                echo View::forge('dev/index', ['error' => 'Wrong password, try again']);
                exit();
            }
        } else {
            echo View::forge('dev/index');
            exit();
        }
    }

    public function action_add_missing_modules()
    {
        $whitelabels = Model_Whitelabel::find_all();
        foreach ($whitelabels as $whitelabel) {
            $user = Model_Admin_User::find_one_by_username(mb_strtolower($whitelabel['name']));
            $modules = [7, 8];
            if ($user !== null) {
                foreach ($modules as $module) {
                    Model_Admin_Modules::add_module_access_to_user($whitelabel->id, $module, $user->id);
                }
            }
        }
    }

    public function action_check_user_balance()
    {
        $whitelabel_id = Input::get('whitelabel');
        $token = Input::get('token');

        if (empty($whitelabel_id) || empty($token)) {
            echo "White-label and token cannot be empty";
            exit();
        }

        $user = Model_Whitelabel_User::find_by([
            "whitelabel_id" => $whitelabel_id,
            "token" => $token
        ]);

        if (!is_null($user) && !empty($user)) {
            $user = $user[0];
            echo "User currency: " . $user['currency_id'] . "<br>";
            echo "User balance: " . $user['balance'] . "<br>";

            $sql = DB::query("SELECT SUM(amount) AS sum FROM whitelabel_transaction WHERE whitelabel_user_id = :user AND type = 1 AND status = 1");
            $sql->param(":user", $user->id);
            $deposits = $sql->execute()->as_array();
            $deposits = $deposits[0]['sum'] ?? 0;
            echo "Deposits sum: " . $deposits . "<br>";

            $sql = DB::query("SELECT SUM(amount) AS sum FROM whitelabel_transaction WHERE whitelabel_user_id = :user AND type = 0 AND payment_method_type = 1 AND status = 1");
            $sql->param(":user", $user->id);
            $balance_purchases = $sql->execute()->as_array();
            $balance_purchases = $balance_purchases[0]['sum'] ?? 0;
            echo "Balance purchase sum: " . $balance_purchases . "<br>";

            $sql = DB::query("SELECT SUM(amount) AS sum FROM whitelabel_transaction WHERE whitelabel_user_id = :user AND type = 0 AND payment_method_type != 1 AND status = 1");
            $sql->param(":user", $user->id);
            $other_purchase = $sql->execute()->as_array();
            $other_purchase = $other_purchase[0]['sum'] ?? 0;
            echo "Other purchase sum: " . $other_purchase . "<br>";

            $sql = DB::query("SELECT SUM(prize_net) AS sum FROM whitelabel_user_ticket WHERE whitelabel_user_id = :user AND paid = 1 AND status = 1");
            $sql->param(":user", $user->id);
            $prize = $sql->execute()->as_array();
            $prize = ($prize[0]['sum'] ?? 0);
            echo "Prize sum: " . $prize . "<br>";

            $balance_check = $deposits - $balance_purchases + $prize;
            echo "Balance check: " . $balance_check;
        } else {
            exit("Cannot find user");
        }
    }

    public function action_unlock_lotteries()
    {
        $whitelabel_id = Input::get('whitelabel');
        if (empty($whitelabel_id)) {
            echo "White-label cannot be empty.";
            exit();
        }
        $currency_id = Input::get('currency');
        $add = $join = "";

        if (!empty($currency_id)) {
            $add = " AND currency_id = " . $currency_id;
            $join = "LEFT JOIN lottery l ON l.id = whitelabel_lottery.lottery_id ";
        }

        DB::query("UPDATE whitelabel_lottery " . $join . "SET ltech_lock = 0 WHERE whitelabel_id = " . $whitelabel_id . $add)->execute();

        Lotto_Helper::clear_cache(['model_lottery', 'model_whitelabel']);
    }

    /**
     * Check for lottorisq account balance
     */
    public function action_lottorisqbalance()
    {
        set_time_limit(0);

        $form = new Forms_Admin_Whitelabels_Ltech();
        $form->check_lottorisqbalance(true);
    }

    public function action_accept_transaction()
    {
        $whitelabel_id = Input::get('whitelabel');
        if (empty($whitelabel_id) || !is_numeric($whitelabel_id) || !($whitelabel_id > 0)) {
            exit("Bad Whitelabel id");
        }

        $transaction_token = Input::get('token');
        if (empty($whitelabel_id)) {
            exit("Bad Transaction token");
        }

        $transaction = Model_Whitelabel_Transaction::find_one_by([
            "whitelabel_id" => $whitelabel_id,
            "token" => $transaction_token
        ]);
        if (empty($transaction)) {
            exit("No transaction with given token");
        }

        $whitelabel = Model_Whitelabel::find_by_pk($whitelabel_id)->to_array();
        if (empty($whitelabel)) {
            exit("No whitelabel with given id");
        }

        $data = [];
        $out_id = Input::get('out_id') ?? '';

        $accept_transaction_result = Lotto_Helper::accept_transaction(
            $transaction,
            $out_id,
            $data,
            $whitelabel
        );

        // Now transaction returns result as INT value and
        // we can redirect user to fail page or success page
        // or simply inform system about that fact
        if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
            ;
        }

        echo "Transaction {$transaction_token} accepted";
    }

    public function action_check_wordpress_language()
    {
        $config = file(APPPATH . "../../../wordpress/wp-config.php");
        $dbconfig = [];
        foreach ($config as $line) {
            if (preg_match("/define\('([A-Za-z_]+)', (.*?)\);/", $line, $m)) {
                if (in_array($m[1], ['DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST'])) {
                    $dbconfig[$m[1]] = $m[2];
                }
            }
        }
        $dsn = 'mysql:dbname=' . $dbconfig['DB_NAME'] . ';host=' . $dbconfig['DB_HOST'];
        $user = $dbconfig['DB_USER'];
        $password = $dbconfig['DB_PASSWORD'];

        try {
            $dbh = new PDO($dsn, $user, $password);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }

        // TODO
    }

    public function action_cachereset()
    {
        Cache::delete_all();
        return "Cache reset ok";
    }

    public function action_add_au_lotteries()
    {
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            $oz_lotto = Model_Whitelabel_Lottery::forge();
            $oz_lotto->whitelabel_id = $whitelabel->id;
            $oz_lotto->lottery_id = 10;
            $oz_lotto->lottery_provider_id = 22;
            $oz_lotto->is_enabled = 0;
            $oz_lotto->model = 0;
            $oz_lotto->income = 1;
            $oz_lotto->income_type = 0;
            $oz_lotto->tier = 0;
            $oz_lotto->volume = 1000;
            $oz_lotto->min_lines = 1; // TODO: check this
            $oz_lotto->save();

            $powerball = Model_Whitelabel_Lottery::forge();
            $powerball->whitelabel_id = $whitelabel->id;
            $powerball->lottery_id = 11;
            $powerball->lottery_provider_id = 23;
            $powerball->is_enabled = 0;
            $powerball->model = 0;
            $powerball->income = 1;
            $powerball->income_type = 0;
            $powerball->tier = 0;
            $powerball->volume = 1000;
            $powerball->min_lines = 1;
            $powerball->save();

            $sat = Model_Whitelabel_Lottery::forge();
            $sat->whitelabel_id = $whitelabel->id;
            $sat->lottery_id = 12;
            $sat->lottery_provider_id = 24;
            $sat->is_enabled = 0;
            $sat->model = 0;
            $sat->income = 1;
            $sat->income_type = 0;
            $sat->tier = 0;
            $sat->volume = 1000;
            $sat->min_lines = 1;
            $sat->save();

            $mon = Model_Whitelabel_Lottery::forge();
            $mon->whitelabel_id = $whitelabel->id;
            $mon->lottery_id = 13;
            $mon->lottery_provider_id = 25;
            $mon->is_enabled = 0;
            $mon->model = 0;
            $mon->income = 1;
            $mon->income_type = 0;
            $mon->tier = 0;
            $mon->volume = 1000;
            $mon->min_lines = 1;
            $mon->save();
        }
    }

    public function action_add_la_primitiva_and_bonolotto()
    {
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            $la_primitiva = Model_Whitelabel_Lottery::forge();
            $la_primitiva->whitelabel_id = $whitelabel->id;
            $la_primitiva->lottery_id = 8;
            $la_primitiva->lottery_provider_id = 20;
            $la_primitiva->is_enabled = 0;
            $la_primitiva->model = 0;
            $la_primitiva->income = 1;
            $la_primitiva->income_type = 0;
            $la_primitiva->tier = 0;
            $la_primitiva->volume = 1000;
            $la_primitiva->min_lines = 1;
            $la_primitiva->save();

            $bonolotto = Model_Whitelabel_Lottery::forge();
            $bonolotto->whitelabel_id = $whitelabel->id;
            $bonolotto->lottery_id = 9;
            $bonolotto->lottery_provider_id = 21;
            $bonolotto->is_enabled = 0;
            $bonolotto->model = 0;
            $bonolotto->income = 1;
            $bonolotto->income_type = 0;
            $bonolotto->tier = 0;
            $bonolotto->volume = 1000;
            $bonolotto->min_lines = 2;
            $bonolotto->save();
        }
    }

    public function action_add_elgordo()
    {
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            $elgordo = Model_Whitelabel_Lottery::forge();
            $elgordo->whitelabel_id = $whitelabel->id;
            $elgordo->lottery_id = 14;
            $elgordo->lottery_provider_id = 26;
            $elgordo->is_enabled = 0;
            $elgordo->model = 0;
            $elgordo->income = 1;
            $elgordo->income_type = 0;
            $elgordo->tier = 0;
            $elgordo->volume = 1000;
            $elgordo->min_lines = 1;
            $elgordo->save();
        }
        echo "OK!";
    }

    public function action_add_floridalotto()
    {
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            $whitelabelLotteryModel = Model_Whitelabel_Lottery::forge();
            $whitelabelLotteryModel->whitelabel_id = $whitelabel->id;
            $whitelabelLotteryModel->lottery_id = 21;
            $whitelabelLotteryModel->lottery_provider_id = 33;
            $whitelabelLotteryModel->is_enabled = 1;
            $whitelabelLotteryModel->model = 0;
            $whitelabelLotteryModel->income = 1;
            $whitelabelLotteryModel->income_type = 0;
            $whitelabelLotteryModel->tier = 0;
            $whitelabelLotteryModel->volume = 1000;
            $whitelabelLotteryModel->min_lines = 1;
            if ($whitelabel->username == "somoslotto") {
                $whitelabelLotteryModel->should_decrease_prepaid = 0;
            }
            $whitelabelLotteryModel->save();
        }
        echo "OK!";
    }

    public function action_add_megasena()
    {
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            $whitelabelLotteryModel = Model_Whitelabel_Lottery::forge();
            $whitelabelLotteryModel->whitelabel_id = $whitelabel->id;
            $whitelabelLotteryModel->lottery_id = 22;
            $whitelabelLotteryModel->lottery_provider_id = 34;
            $whitelabelLotteryModel->is_enabled = 1;
            $whitelabelLotteryModel->model = 0;
            $whitelabelLotteryModel->income = 1;
            $whitelabelLotteryModel->income_type = 0;
            $whitelabelLotteryModel->tier = 0;
            $whitelabelLotteryModel->volume = 1000;
            $whitelabelLotteryModel->min_lines = 1;
            if ($whitelabel->username == "somoslotto") {
                $whitelabelLotteryModel->should_decrease_prepaid = 0;
            }
            $whitelabelLotteryModel->save();
        }
        echo "OK!";
    }

    public function action_add_quina()
    {
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            $whitelabelLotteryModel = Model_Whitelabel_Lottery::forge();
            $whitelabelLotteryModel->whitelabel_id = $whitelabel->id;
            $whitelabelLotteryModel->lottery_id = 23;
            $whitelabelLotteryModel->lottery_provider_id = 35;
            $whitelabelLotteryModel->is_enabled = 1;
            $whitelabelLotteryModel->model = 0;
            $whitelabelLotteryModel->income = 1;
            $whitelabelLotteryModel->income_type = 0;
            $whitelabelLotteryModel->tier = 0;
            $whitelabelLotteryModel->volume = 1000;
            $whitelabelLotteryModel->min_lines = 1;
            if ($whitelabel->username == "somoslotto") {
                $whitelabelLotteryModel->should_decrease_prepaid = 0;
            }
            $whitelabelLotteryModel->save();
        }
        echo "OK!";
    }

    public function action_add_lottoamerica()
    {
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            $whitelabelLotteryModel = Model_Whitelabel_Lottery::forge();
            $whitelabelLotteryModel->whitelabel_id = $whitelabel->id;
            $whitelabelLotteryModel->lottery_id = 28;
            $whitelabelLotteryModel->lottery_provider_id = 40;
            $whitelabelLotteryModel->is_enabled = true;
            $whitelabelLotteryModel->model = 0;
            $whitelabelLotteryModel->income = 1;
            $whitelabelLotteryModel->income_type = 0;
            $whitelabelLotteryModel->tier = 0;
            $whitelabelLotteryModel->volume = 1000;
            $whitelabelLotteryModel->min_lines = 1;
            if ($whitelabel->username == "somoslotto") {
                $whitelabelLotteryModel->should_decrease_prepaid = 0;
            }
            $whitelabelLotteryModel->save();
        }
        echo "OK!";
    }

    public function action_add_lotto_at()
    {
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            $whitelabelLotteryModel = Model_Whitelabel_Lottery::forge();
            $whitelabelLotteryModel->whitelabel_id = $whitelabel->id;
            $whitelabelLotteryModel->lottery_id = 29;
            $whitelabelLotteryModel->lottery_provider_id = 41;
            $whitelabelLotteryModel->is_enabled = true;
            $whitelabelLotteryModel->model = 0;
            $whitelabelLotteryModel->income = 1;
            $whitelabelLotteryModel->income_type = 0;
            $whitelabelLotteryModel->tier = 0;
            $whitelabelLotteryModel->volume = 1000;
            $whitelabelLotteryModel->min_lines = 1;
            if ($whitelabel->username == "somoslotto") {
                $whitelabelLotteryModel->should_decrease_prepaid = 0;
            }
            $whitelabelLotteryModel->save();
        }
        echo "OK!";
    }

    public function action_add_setforlife_uk()
    {
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            $whitelabelLotteryModel = Model_Whitelabel_Lottery::forge();
            $whitelabelLotteryModel->whitelabel_id = $whitelabel->id;
            $whitelabelLotteryModel->lottery_id = 26;
            $whitelabelLotteryModel->lottery_provider_id = 38;
            $whitelabelLotteryModel->is_enabled = true;
            $whitelabelLotteryModel->model = 0;
            $whitelabelLotteryModel->income = 1;
            $whitelabelLotteryModel->income_type = 0;
            $whitelabelLotteryModel->tier = 0;
            $whitelabelLotteryModel->volume = 1000;
            $whitelabelLotteryModel->min_lines = 1;
            if ($whitelabel->username == "somoslotto") {
                $whitelabelLotteryModel->should_decrease_prepaid = 0;
            }
            $whitelabelLotteryModel->save();
        }
        echo "OK!";
    }

    public function action_add_thunderball()
    {
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            $whitelabelLotteryModel = Model_Whitelabel_Lottery::forge();
            $whitelabelLotteryModel->whitelabel_id = $whitelabel->id;
            $whitelabelLotteryModel->lottery_id = 27;
            $whitelabelLotteryModel->lottery_provider_id = 39;
            $whitelabelLotteryModel->is_enabled = true;
            $whitelabelLotteryModel->model = 0;
            $whitelabelLotteryModel->income = 1;
            $whitelabelLotteryModel->income_type = 0;
            $whitelabelLotteryModel->tier = 0;
            $whitelabelLotteryModel->volume = 1000;
            $whitelabelLotteryModel->min_lines = 1;
            if ($whitelabel->username == "somoslotto") {
                $whitelabelLotteryModel->should_decrease_prepaid = 0;
            }
            $whitelabelLotteryModel->save();
        }
        echo "OK!";
    }

    public function action_add_6aus49()
    {
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            $whitelabelLotteryModel = Model_Whitelabel_Lottery::forge();
            $whitelabelLotteryModel->whitelabel_id = $whitelabel->id;
            $whitelabelLotteryModel->lottery_id = 30;
            $whitelabelLotteryModel->lottery_provider_id = 42;
            $whitelabelLotteryModel->is_enabled = true;
            $whitelabelLotteryModel->model = 0;
            $whitelabelLotteryModel->income = 1;
            $whitelabelLotteryModel->income_type = 0;
            $whitelabelLotteryModel->tier = 0;
            $whitelabelLotteryModel->volume = 1000;
            $whitelabelLotteryModel->min_lines = 1;
            if ($whitelabel->username == "somoslotto") {
                $whitelabelLotteryModel->should_decrease_prepaid = 0;
            }
            $whitelabelLotteryModel->save();
        }
        echo "OK!";
    }

    public function action_add_lotto_fr()
    {
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            $elgordo = Model_Whitelabel_Lottery::forge();
            $elgordo->whitelabel_id = $whitelabel->id;
            $elgordo->lottery_id = 15;
            $elgordo->lottery_provider_id = 27;
            $elgordo->is_enabled = 0;
            $elgordo->model = 0;
            $elgordo->income = 1;
            $elgordo->income_type = 0;
            $elgordo->tier = 0;
            $elgordo->volume = 1000;
            $elgordo->min_lines = 1;
            $elgordo->save();
        }
        echo "OK!";
    }

    public function action_add_otoslotto()
    {
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            $whitelabelLotteryModel = Model_Whitelabel_Lottery::forge();
            $whitelabelLotteryModel->whitelabel_id = $whitelabel->id;
            $whitelabelLotteryModel->lottery_id = 24;
            $whitelabelLotteryModel->lottery_provider_id = 36;
            $whitelabelLotteryModel->is_enabled = true;
            $whitelabelLotteryModel->model = 0;
            $whitelabelLotteryModel->income = 1;
            $whitelabelLotteryModel->income_type = 0;
            $whitelabelLotteryModel->tier = 0;
            $whitelabelLotteryModel->volume = 1000;
            $whitelabelLotteryModel->min_lines = 1;
            $whitelabelLotteryModel->save();
        }
        echo "OK!";
    }

    public function action_add_hatoslotto()
    {
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            $whitelabelLotteryModel = Model_Whitelabel_Lottery::forge();
            $whitelabelLotteryModel->whitelabel_id = $whitelabel->id;
            $whitelabelLotteryModel->lottery_id = 25;
            $whitelabelLotteryModel->lottery_provider_id = 37;
            $whitelabelLotteryModel->is_enabled = true;
            $whitelabelLotteryModel->model = 0;
            $whitelabelLotteryModel->income = 1;
            $whitelabelLotteryModel->income_type = 0;
            $whitelabelLotteryModel->tier = 0;
            $whitelabelLotteryModel->volume = 1000;
            $whitelabelLotteryModel->min_lines = 1;
            $whitelabelLotteryModel->save();
        }
        echo "OK!";
    }

    public function action_add_skandinav_lotto()
    {
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            $whitelabelLotteryModel = Model_Whitelabel_Lottery::forge();
            $whitelabelLotteryModel->whitelabel_id = $whitelabel->id;
            $whitelabelLotteryModel->lottery_id = 31;
            $whitelabelLotteryModel->lottery_provider_id = 43;
            $whitelabelLotteryModel->is_enabled = true;
            $whitelabelLotteryModel->model = 0;
            $whitelabelLotteryModel->income = 1;
            $whitelabelLotteryModel->income_type = 0;
            $whitelabelLotteryModel->tier = 0;
            $whitelabelLotteryModel->volume = 1000;
            $whitelabelLotteryModel->min_lines = 1;
            $whitelabelLotteryModel->save();
        }
        echo "OK!";
    }

    public function action_add_lotto_multi_multi()
    {
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            $whitelabelLotteryModel = Model_Whitelabel_Lottery::forge();
            $whitelabelLotteryModel->whitelabel_id = $whitelabel->id;
            $whitelabelLotteryModel->lottery_id = 32;
            $whitelabelLotteryModel->lottery_provider_id = 44;
            $whitelabelLotteryModel->is_enabled = true;
            $whitelabelLotteryModel->model = 0;
            $whitelabelLotteryModel->income = 1;
            $whitelabelLotteryModel->income_type = 0;
            $whitelabelLotteryModel->tier = 0;
            $whitelabelLotteryModel->volume = 1000;
            $whitelabelLotteryModel->min_lines = 1;
            $whitelabelLotteryModel->save();
        }
        echo "OK!";
    }

    public function action_lottohoyuserimport()
    {
        // save it as UTF-8 with UNIX end-lines before!
        try {
            $whitelabel = Model_Whitelabel::find_by_pk(3); // lottohoy
            $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel);
            $whitelabel_languages_indexed_by_code = [];
            foreach ($whitelabel_languages as $lang) {
                $whitelabel_languages_indexed_by_code[mb_substr($lang['code'], 0, 2)] = $lang;
            }
            $i = -1;

            $val = Validation::forge();

            $val->add("profile.name", "Name")->add_rule('trim')
                ->add_rule('stripslashes')->add_rule('min_length', 3)->add_rule('max_length', 100)->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8']);
            $val->add("profile.surname", "Surname")->add_rule('trim')
                ->add_rule('stripslashes')->add_rule('min_length', 3)->add_rule('max_length', 100)->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'singlequotes', 'utf8']);
            $val->add("profile.address", "Address")->add_rule('trim')
                ->add_rule('stripslashes')->add_rule('min_length', 3)->add_rule('max_length', 100)->add_rule('valid_string', ['alpha', 'specials', 'numeric', 'dashes', 'spaces', 'commas', 'dots', 'forwardslashes', 'utf8']);
            $val->add("profile.city", 'City')->add_rule('trim')
                ->add_rule('stripslashes')->add_rule('min_length', 3)->add_rule('max_length', 100)->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);
            //$val->add("profile.state", 'Region')->add_rule('trim')
            //      ->add_rule('stripslashes')->add_rule('valid_string', array('alpha', 'numeric', 'dashes'));
            $val->add("profile.country", "Country")->add_rule('trim')
                ->add_rule('stripslashes')->add_rule('exact_length', 2)->add_rule('valid_string', ['alpha']);

            $val->add("profile.email", "E-mail")->add_rule('trim')->add_rule('stripslashes')
                ->add_rule('required')->add_rule('valid_email');

            $val->add("profile.zip", "Postal Code")->add_rule('trim')
                ->add_rule('stripslashes')->add_rule('max_length', 20)->add_rule('valid_string', ['alpha', 'numeric', 'dashes', 'spaces']);

            $val->add("profile.phone", "Phone")->add_rule('trim')
                ->add_rule('stripslashes')->add_rule('min_length', 3)->add_rule('max_length', 100)->add_rule('valid_string', ['numeric', 'dashes', 'spaces']);

            $countries = Lotto_Helper::get_localized_country_list();
            if (($handle = fopen(APPPATH . '../../../dev/lottohoy/lhdb.csv', "r")) !== false) {
                while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                    $i++;
                    if ($i == 0) {
                        continue;
                    }
                    $user = Model_Whitelabel_User::forge();

                    // LANGUAGE
                    if (isset($whitelabel_languages_indexed_by_code[mb_strtolower($data[0])])) {
                        $language = $whitelabel_languages_indexed_by_code[mb_strtolower($data[0])];
                        $user->lanugage_id = $language['id'];
                    } else {
                        $user->language_id = 1;
                    }
                    $user->token = Lotto_Security::generate_user_token(3);
                    $user->whitelabel_id = 3;
                    $user->currency_id = 2;
                    $user->is_active = 1;


                    // NAME
                    $name = Normalizer::normalize($data[1], Normalizer::FORM_KC);
                    $surname = Normalizer::normalize($data[2], Normalizer::FORM_KC);
                    $address = Normalizer::normalize($data[3], Normalizer::FORM_KC);
                    $zip = Normalizer::normalize($data[4], Normalizer::FORM_KC);
                    $city = Normalizer::normalize($data[5], Normalizer::FORM_KC);
                    $state = Normalizer::normalize($data[6], Normalizer::FORM_KC);
                    $country = Normalizer::normalize($data[7], Normalizer::FORM_KC);
                    //$phone = Normalizer::normalize($data[8], Normalizer::FORM_KC);
                    $email = Normalizer::normalize($data[8], Normalizer::FORM_KC);
                    $balance = round(str_replace(",", ".", $data[10]), 2);
                    //$wbalance = number_format(str_replace(",", ".", $data[13]), 2, ".", "");
                    //$total_balance = number_format(bcadd($balance, $wbalance, 2), 2, ".", "");
                    $date_register = DateTime::createFromFormat("d/m/Y H:i:s", $data[12] . ' 00:00:00', new DateTimeZone("UTC"));
                    $is_confirmed = $data[13] == "Yes" ? true : false;
                    $birthdate = DateTime::createFromFormat("d/m/Y H:i:s", $data[14] . ' 00:00:00', new DateTimeZone("UTC"));

                    if ($date_register == false) {
                        var_dump($data);
                        exit();
                    }


                    $post = ['profile' => [
                        'name' => $name,
                        'surname' => $surname,
                        'address' => $address,
                        'zip' => $zip,
                        'city' => $city,
                        'state' => $state,
                        'country' => $country,
                        //'phone' => $phone,
                        'email' => $email,
                        'phone_country' => $country,
                        'balance' => $balance,
                        'date_register' => $date_register->format("Y-m-d H:i:s"),
                        'is_confirmed' => $is_confirmed,
                        'birthdate' => $birthdate != false ? $birthdate->format("Y-m-d") : null
                    ]];


                    $val->run($post);
                    $errors = Lotto_Helper::generate_errors($val->error());
                    if ($val->validated('profile.email')) {
                        $set['email'] = $val->validated('profile.email');
                        $set['balance'] = $balance;
                        $set['date_register'] = $date_register->format("Y-m-d H:i:s");
                        $set['is_confirmed'] = $is_confirmed;


                        $now = new DateTime("now", new DateTimeZone("UTC"));
                        $newsalt = Lotto_Security::generate_salt();
                        $hash = Lotto_Security::generate_hash(Str::random('alnum', 10), $newsalt);

                        $user->last_update = $now->format("Y-m-d H:i:s");
                        $user->salt = $newsalt;
                        $user->hash = $hash;


                        if ($birthdate !== false) {
                            $set['birthdate'] = $birthdate->format("Y-m-d");
                        }

                        if ($val->validated('profile.name')) {
                            $set['name'] = $val->validated('profile.name');
                        } else {
                            $set['name'] = '';
                        }

                        if ($val->validated('profile.surname')) {
                            $set['surname'] = $val->validated('profile.surname');
                        } else {
                            $set['surname'] = '';
                        }

                        if ($val->validated('profile.address')) {
                            $set['address_1'] = $val->validated('profile.address');
                            $set['address_2'] = '';
                        } else {
                            $set['address_1'] = '';
                            $set['address_2'] = '';
                        }

                        if ($val->validated('profile.zip')) {
                            $set['zip'] = $val->validated('profile.zip');
                        } else {
                            $set['zip'] = '';
                        }

                        if ($val->validated('profile.city')) {
                            $set['city'] = $val->validated('profile.city');
                        } else {
                            $set['city'] = '';
                        }

                        if ($val->validated('profile.country')) {
                            if ($val->validated('profile.country') === "" || isset($countries[$val->validated('profile.country')])) {
                                $set['country'] = $val->validated('profile.country');
                            }
                        } else {
                            $set['country'] = '';
                        }

                        if ($val->validated('profile.state')) {
                            if ($val->validated('profile.state') == "" || ($set['country'] !== "" && Lotto_Helper::check_region($val->validated("profile.state"), $set['country']))) {
                                $set['state'] = $val->validated('profile.state');
                            }
                        } else {
                            $set['state'] = '';
                        }
                        $set['phone_country'] = '';
                        $set['phone'] = '';
                        $set['timezone'] = '';
                        $set['register_ip'] = '127.0.0.1';
                        $set['last_ip'] = '127.0.0.1';
                        $set['last_active'] = $now->format("Y-m-d H:i:s");


//                        if ($val->validated('profile.phone')) {
//                            if ($val->validated('profile.country') === "" || isset($countries[$val->validated('profile.country')])) {
//                                $phone = $val->validated('profile.phone');
//                                $phone_country = "";
//                                if (!empty($phone)) {
//
//                                    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
//                                    try {
//                                        $pNumber = $phoneUtil->parse($phone, $country);
//                                        $isValid = $phoneUtil->isValidNumber($pNumber);
//                                        if ($isValid) {
//                                            $phone = $phoneUtil->format($pNumber, \libphonenumber\PhoneNumberFormat::E164);
//                                            $phone_country = $country;
//                                        } else {
//                                            $errors['profile.phone'] = "Wrong phone number!";
//                                        }
//                                    } catch (\libphonenumber\NumberParseException $e) {
//                                        $errors['profile.phone'] = "Wrong phone number!";
//                                    }
//                                }
//                            } else {
//                                $errors['profile.country'] = "Bad country";
//                            }
//                        }

                        $user->set($set);
                        $user->save();
                    }
                    if (count($errors)) {
                        echo 'User ' . $email . ' has errors:';
                        foreach ($errors as $key => $error) {
                            $keyx = explode(".", $key);
                            echo '<br>' . $key . ': ' . $error . ' (' . Security::htmlentities($post['profile'][$keyx[1]]) . ')';
                        }
                        echo '<br><br>';
                    }
                }
                fclose($handle);
            }
        } catch (Exception $e) {
            var_dump($e);
        }
    }

    public function action_calculatepaymentcosts()
    {
        exit('disabled');
        set_time_limit(0);

        // remove invalid transactions that should not be in the database
        DB::query("DELETE FROM whitelabel_transaction WHERE payment_method_type IS NULL")->execute();

        $currencies = Helpers_Currency::getCurrencies();
        $transactions = Model_Whitelabel_Transaction::find([
            "where" => [
                "status" => 1
            ]]);
        foreach ($transactions as $transaction) {
            $cost_percent = $cost_fixed = $cost_currency_id = 0;
            switch ($transaction['payment_method_type']) {
                case 2: // cc
                    $whitelabel_payment_methods_without_currency = Model_Whitelabel_CC_Method::get_cc_methods_for_whitelabel(['id' => $transaction->whitelabel_id]);
                    if (empty($transaction->whitelabel_cc_method_id)) {
                        echo 'No CC data: ' . $transaction->id . '<br>';
                        continue 2;
                    }
                    $cost_percent = $whitelabel_payment_methods_without_currency[$transaction->whitelabel_cc_method_id]['cost_percent'];
                    $cost_fixed = $whitelabel_payment_methods_without_currency[$transaction->whitelabel_cc_method_id]['cost_fixed'];
                    $cost_currency_id = $whitelabel_payment_methods_without_currency[$transaction->whitelabel_cc_method_id]['cost_currency_id'];
                    break;
                case 3: // other
                    $whitelabel_payment_methods_without_currency = Model_Whitelabel_Payment_Method::get_payment_methods_for_whitelabel(['id' => $transaction->whitelabel_id]);
                    $cost_percent = $whitelabel_payment_methods_without_currency[$transaction->whitelabel_payment_method_id]['cost_percent'];
                    $cost_fixed = $whitelabel_payment_methods_without_currency[$transaction->whitelabel_payment_method_id]['cost_fixed'];
                    $cost_currency_id = $whitelabel_payment_methods_without_currency[$transaction->whitelabel_payment_method_id]['cost_currency_id'];
                    break;
            }

            /* calculate payment cost */
            $calc_percent = $calc_fixed = 0;
            $calc_percent_usd = $calc_fixed_usd = 0;
            if (!empty($cost_percent) && $cost_percent != "0.00") {
                $calc_percent = bcmul(bcdiv($cost_percent, 100, 4), $transaction->amount, 4);
                $calc_percent_usd = Helpers_Currency::convert_to_USD($calc_percent, "EUR");
            }
            if (!empty($cost_fixed) && $cost_percent != "0.00" && !empty($cost_currency_id)) {
                $calc_fixed = $cost_fixed;
                // convert to USD
                $calc_fixed_usd = Helpers_Currency::convert_to_USD($calc_fixed, $currencies[$cost_currency_id]['code']);
                // convert to website currency (EUR)
                $calc_fixed = Helpers_Currency::convert_to_EUR($calc_fixed_usd, "USD");
            }

            $total_calc_cost = bcadd($calc_percent, $calc_fixed, 4);
            $total_calc_cost_usd = bcadd($calc_percent_usd, $calc_fixed_usd, 4);

            $transaction->payment_cost = number_format($total_calc_cost, 2, ".", "");
            $transaction->payment_cost_usd = number_format($total_calc_cost_usd, 2, ".", "");

            $transaction->save();
        }
    }

    public function action_updateprices()
    {
        exit('disabled');
        set_time_limit(0);
        $currencies = Helpers_Currency::getCurrencies();
        $currency = $currencies[2];

        $tickets = Model_Whitelabel_User_Ticket::find_all();
        foreach ($tickets as $ticket) {
            $lines = Model_Whitelabel_User_Ticket_Line::find_by_whitelabel_user_ticket_id($ticket['id']);
            $wldb = Model_Whitelabel::find_by_pk($ticket['whitelabel_id']);
            $whitelabel = Model_Whitelabel::get_by_domain($wldb['domain']);
            $user = Model_Whitelabel_User::find_by_pk($ticket['whitelabel_user_id']);

            $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);
            $lottery = $lotteries['__by_id'][$ticket['lottery_id']];

            /*** price calculations ***/
            $itm_price = bcdiv($ticket['amount'], !empty($lines) ? count($lines) : 0, 4);
            $itm_price_usd = bcdiv($ticket['amount_usd'], !empty($lines) ? count($lines) : 0, 4);
            $itm_price_local = Helpers_Currency::convert_to_any($itm_price_usd, "USD", $lottery['currency']);

            $price = bcmul($itm_price, !empty($lines) ? count($lines) : 0, 4);
            $price_usd = bcmul($itm_price_usd, !empty($lines) ? count($lines) : 0, 4);
            $price_local = bcmul($itm_price_local, !empty($lines) ? count($lines) : 0, 4);

            $calc_cost = Lotto_Helper::get_price($lottery, 0, 0, 1000);

            $cost_local = bcmul(bcadd($calc_cost[0], $calc_cost[1], 4), !empty($lines) ? count($lines) : 0, 4);
            $cost_usd = Helpers_Currency::convert_to_USD($cost_local, $lottery['currency']);
            $cost = Helpers_Currency::convert_to_EUR($cost_local, $lottery['currency']);

            $income_local = bcsub($price_local, $cost_local, 4);
            $income_usd = bcsub($price_usd, $cost_usd, 4);
            $income = bcsub($price, $cost, 4);

            $income_value = $lottery['income'];
            $income_type = $lottery['income_type'];

            $margin_value = $whitelabel['margin'];

            $margin_local = number_format(bcmul($income_local, bcdiv($whitelabel['margin'], "100", 4), 4), 2, ".", "");
            $margin_usd = number_format(bcmul($income_usd, bcdiv($whitelabel['margin'], "100", 4), 4), 2, ".", "");
            $margin = number_format(bcmul($income, bcdiv($whitelabel['margin'], "100", 4), 4), 2, ".", "");

            if ($margin_local < 0) {
                $margin_local = 0;
            }
            if ($margin_usd < 0) {
                $margin_usd = 0;
            }
            if ($margin < 0) {
                $margin = 0;
            }

            /*** end of price calculations ***/

            $ticket->set([
                'amount_local' => number_format($price_local, 2, ".", ""),
                'is_insured' => 0,
                'tier' => 0,
                'cost_local' => number_format($cost_local, 2, ".", ""),
                'cost_usd' => number_format($cost_usd, 2, ".", ""),
                "cost" => number_format($cost, 2, ".", ""),
                "income_local" => number_format($income_local, 2, ".", ""),
                "income_usd" => number_format($income_usd, 2, ".", ""),
                "income" => number_format($income, 2, ".", ""),
                "income_value" => $income_value,
                "income_type" => $income_type,
                "margin_value" => $margin_value,
                "margin_local" => $margin_local,
                "margin_usd" => $margin_usd,
                "margin" => $margin,
                "ip" => $user['last_ip']
            ]);
            $ticket->save();

            foreach ($lines as $line) {
                $line->set([
                    'amount_local' => number_format(Helpers_Currency::convert_to_any($line['amount_usd'], "USD", $lottery['currency']), 2, ".", "")
                ]);
                $line->save();
            }
        }


        $transactions = Model_Whitelabel_Transaction::find_all();
        foreach ($transactions as $transaction) {
            if ($transaction->type == 0) {
                $res = DB::query("SELECT SUM(amount) AS amount, SUM(amount_usd) AS amount_usd, SUM(cost) AS cost, SUM(cost_usd) AS cost_usd, SUM(income) AS income, SUM(income_usd) AS income_usd, SUM(margin) AS margin, SUM(margin_usd) AS margin_usd FROM whitelabel_user_ticket WHERE whitelabel_transaction_id = :transaction GROUP BY whitelabel_transaction_id");
                $res->param(":transaction", $transaction->id);
                $res = $res->execute()->as_array();
                $res = $res[0];
                //$transaction->amount = $res['amount'];
                //$transaction->amount_usd = $res['amount_usd'];
                $transaction->cost = $res['cost'];
                $transaction->cost_usd = $res['cost_usd'];
                $transaction->income = $res['income'];
                $transaction->income_usd = $res['income_usd'];
                $transaction->margin = $res['margin'];
                $transaction->margin_usd = $res['margin_usd'];
                $transaction->save();
            }
        }
    }

    public function action_optimizeimages()
    {
        exit("disabled");
        set_time_limit(0);
        $dirname = "whitelotto_work";
        if (\Fuel::$env == \Fuel::PRODUCTION) {
            $dirname = "whitelotto";
        }

        /* first step is to get all files that are not miniatures
         * it is a bit tricky because our whitelabels uploaded filenames like xxxx-750x800.jpg
         * so those files look like miniature but they are not!
         * that is why we have to make it two-pass
         */
        $miniatures = [];
        $main_images = [];

        $dir = new DirectoryIterator('/var/www/' . $dirname . '/wordpress/wp-content/uploads/sites/');
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                $dir2 = new DirectoryIterator('/var/www/' . $dirname . '/wordpress/wp-content/uploads/sites/' . $fileinfo->getFilename());
                $dir2_name = $fileinfo->getFilename();
                foreach ($dir2 as $imagefile) {
                    if (!$imagefile->isDir() && !$imagefile->isDot()) {
                        $filename = $imagefile->getFilename();
                        if (preg_match('/(.*)-([0-9]{2,4})x([0-9]{2,4})\.([a-z]{3,4})$/u', $filename, $m)) {
                            if ($m[1] != "cropped-fav" && $m[1] != "fav") {
                                // is miniature
                                $miniatures[] = [$filename, $imagefile->getPath(), $imagefile->getExtension()];
                            }
                        } else {
                            // is not miniature
                            $main_images[] = [$filename, $imagefile->getPath(), $imagefile->getExtension()];
                        }
                    }
                }
            }
        }

        $not_a_miniature = [];
        $new_miniatures = [];
        foreach ($miniatures as $miniature) {
            $name = $miniature[0];
            if (preg_match('/(.*)-([0-9]{2,4})x([0-9]{2,4})-([0-9]{2,4})x([0-9]{2,4})\.([a-z]{3,4})$/u', $name, $m)) {
                // we found double size present in the filename, so the full version of this "miniature" is not a "miniature"
                $found = false;
                foreach ($not_a_miniature as $item) {
                    if ($item[0] == $m[1] . '-' . $m[2] . 'x' . $m[3] . '.' . $m[6]) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $not_a_miniature[] = [$m[1] . '-' . $m[2] . 'x' . $m[3] . '.' . $m[6], $miniature[1], $m[6]];
                }
            }
        }

        foreach ($miniatures as $miniature) {
            $found = false;
            foreach ($not_a_miniature as $item) {
                if ($miniature[0] == $item[0]) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $new_miniatures[] = $miniature;
            }
        }

        $main_images = array_merge($main_images, $not_a_miniature);

        foreach ($main_images as $image) {
            $file = $image[1] . '/' . $image[0];
            $ext = $image[2];
            if ($ext == "png") {
                echo 'optipng -strip all -o5 ' . $file;
                echo '<br>';
            //exec('optipng -strip all -o5 '.$file);
            } elseif ($ext == "jpg" || $ext == "jpeg") {
                echo 'jpegoptim -m90 --strip-all --all-progressive ' . $file;
                echo '<br>';
                //exec('jpegoptim --strip-all --all-normal '.$file);
            }
        }
        foreach ($new_miniatures as $miniature) {
            $file = $miniature[1] . '/' . $miniature[0];
            $ext = $miniature[2];
            if ($ext == "png") {
                echo 'optipng -strip all -o5 ' . $file;
                echo '<br>';
            //exec('optipng -strip all -o5 '.$file);
            } elseif ($ext == "jpg" || $ext == "jpeg") {
                echo 'jpegoptim --strip-all --all-progressive ' . $file;
                echo '<br>';
                //exec('jpegoptim --strip-all --all-normal '.$file);
            }
        }
    }

    public function action_removethumbnails()
    {
        exit("Disabled");
        $dirname = "whitelotto_work";
        if (\Fuel::$env == \Fuel::PRODUCTION) {
            $dirname = "whitelotto";
        }
        $dir = new DirectoryIterator('/var/www/' . $dirname . '/wordpress/wp-content/uploads/sites/');
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                $dir2 = new DirectoryIterator('/var/www/' . $dirname . '/wordpress/wp-content/uploads/sites/' . $fileinfo->getFilename());
                $dir2_name = $fileinfo->getFilename();
                foreach ($dir2 as $imagefile) {
                    $filename = $imagefile->getFilename();
                    if (preg_match('/(.*)-([0-9]{2,4})x([0-9]{2,4})\.[a-z]{3,4}$/u', $filename, $m)) {
                        if ($m[1] != "cropped-fav" && $m[1] != "fav") {
                            var_dump('/var/www/' . $dirname . '/wordpress/wp-content/uploads/sites/' . $dir2_name . '/' . $filename);
                            unlink('/var/www/' . $dirname . '/wordpress/wp-content/uploads/sites/' . $dir2_name . '/' . $filename);
                        }
                    }
                }
            }
        }
    }

    public function action_fixeuromillions()
    {
        // we didn't notice the change of rules back in september 2016...
        // this is the fix for over one year being blind
        exit("Disabled");
        set_time_limit(0);
        $draws = Model_Lottery_Draw::find([
            "where" => [
                "lottery_id" => 6,
                ["date_local", ">=", "2016-09-24 00:00:00"]
            ]
        ]);

        $old_type_data = Model_Lottery_Type_Data::find(
            [
                'where' => [
                    'lottery_type_id' => 6,
                ],
                'order_by' => 'id'
            ]
        );

        $type_data = Model_Lottery_Type_Data::find(
            [
                'where' => [
                    'lottery_type_id' => 9,
                ],
                'order_by' => 'id'
            ]
        );

        $map_data = [];

        foreach ($old_type_data as $old_item) {
            foreach ($type_data as $new_item) {
                if ($old_item['match_n'] == $new_item['match_n'] && $old_item['match_b'] == $new_item['match_b']) {
                    $map_data[$old_item['id']] = $new_item['id'];
                }
            }
        }
        foreach ($draws as $draw) {
            $draw->lottery_type_id = 9;
            $draw->save();

            foreach ($map_data as $old => $new) {
                $res = DB::query("UPDATE lottery_prize_data SET lottery_type_data_id = :new WHERE lottery_draw_id = :draw AND lottery_type_data_id = :old");
                $res->param(":draw", $draw->id);
                $res->param(":new", $new);
                $res->param(":old", $old);
                $res->execute();
            }
        }

        $tickets = Model_Whitelabel_User_Ticket::find([
            "where" => [
                "lottery_id" => 6,
                ["draw_date", ">=", "2016-09-24 00:00:00"]
            ]
        ]);

        foreach ($tickets as $ticket) {
            $ticket->lottery_type_id = 9;
            $ticket->save();
            foreach ($map_data as $old => $new) {
                $res = DB::query("UPDATE whitelabel_user_ticket_line SET lottery_type_data_id = :new WHERE whitelabel_user_ticket_id = :ticket AND lottery_type_data_id = :old");
                $res->param(":ticket", $ticket->id);
                $res->param(":new", $new);
                $res->param(":old", $old);
                $res->execute();
            }
        }
    }

    public function action_baselanguages()
    {
        exit('disabled');
        // this was used to update language files to the newest forms
        $old_language = file(APPPATH . '../../../wordpress/wp-content/plugins/lotto-platform/languages/lotto-platform-fr_FR.po', FILE_IGNORE_NEW_LINES);
        $old_translations = [];
        $original_started = false;
        $translation_started = false;
        foreach ($old_language as $lp => $line) {
            if (strpos($line, "msgid") === 0 && $lp != 0) {
                $old_translations[] = [[$line], []];
                $original_started = true;
            } elseif ($original_started && strpos($line, '"') === 0) {
                $old_translations[count($old_translations) - 1][0][] = $line;
            } elseif ($lp != 1 && (strpos($line, "msgstr") === 0 || ($translation_started && strpos($line, '"') === 0))) {
                if (strpos($line, "msgstr") === 0) {
                    $translation_started = true;
                    $original_started = false;
                }
                $old_translations[count($old_translations) - 1][1][] = $line;
            } else {
                $translation_started = false;
            }
        }

        // now new language (from theme)
        $new_language = file(APPPATH . '../../../wordpress/wp-content/themes/base/languages/fr_FR.po', FILE_IGNORE_NEW_LINES);
        $newfile = [];
        $tosearch = [];
        $original_started = false;
        $translation_started = false;
        $found_translation = false;
        $block = false;
        foreach ($new_language as $lp => $line) {
            // now choose if we need to insert old line or new line
            // this can be hard:>
            if (strpos($line, "msgid") === 0 && $lp != 0) {
                $tosearch = [];
                $tosearch[] = [$line];
                $original_started = true;
                $block = false;
                $newfile[] = $line;
            } elseif ($original_started && strpos($line, '"') === 0) {
                $tosearch[count($tosearch) - 1][] = $line;
                $newfile[] = $line;
            } elseif (!$block && $lp != 1 && (strpos($line, "msgstr") === 0 || ($translation_started && strpos($line, '"') === 0))) {
                if (strpos($line, "msgstr") === 0) {
                    $translation_started = true;
                    $original_started = false;
                }
                // now we need to find the translation if already exists.

                $is_translation = false;
                foreach ($old_translations as $old_translation) {
                    $is_translation = true;
                    $totranslate = $old_translation[0];
                    foreach ($tosearch as $tskey => $ts_item) {
                        foreach ($ts_item as $key => $ot_item) {
                            if (!isset($totranslate[$key]) || $totranslate[$key] != $ot_item) {
                                $is_translation = false;
                            }
                        }
                    }

                    if ($is_translation) {
                        $found_translation = true;
                        // put old lines

                        foreach ($old_translation[1] as $line_item) {
                            $newfile[] = $line_item;
                        }
                        $translation_started = false;
                        $block = true;
                        break;
                    }
                }
                if (!$is_translation) {
                    $newfile[] = $line;
                }
            } else {
                if (strlen($line) == 0) {
                    $block = false;
                }
                if ($block == false) {
                    $newfile[] = $line;
                }
                $translation_started = false;
                $found_translation = false;
            }
        }

        file_put_contents(APPPATH . '../../../wordpress/wp-content/themes/base/languages/fr_FR.new.po', implode("\n", $newfile));
    }

    /**
     * Merge translations from base-theme and validations into lotto-platform
     *
     * GET parameters
     * - locales - comma separated keys of locales which should be merged
     * example: locales=pl_PL,en_GB,nl_NL
     * - overwrite - means to overwrite original files. If it's missing then new files are created. Like lotto-platform.new.po
     * - merge - base or validation or empty to merge both at the same time (timeout possible)
     *
     *
     * NOTE:
     * Script still have problem with multiline duplications, e.g.:
     * msgid ""
     * "I accept the <a href=\"%s\" target=\"_blank\">Terms</a> and the <a href=\"%s\" target=\"_blank\">Policy</"
     * "a>"
     * and
     * msgid ""
     * "I accept the <a href=\"%s\" target=\"_blank\">Terms</a> and the <a href=\"%s\" target=\"_blank"
     * "\">Policy</a>"
     *
     * Look, line breaks are in different spot.
     *
     *
     * @throws Exception
     */
    public function action_merge_translations()
    {
        $wp_content_path = realpath(APPPATH . '../../../wordpress/wp-content');

        $locales = [];
        $base_translations_directory = $wp_content_path . '/themes/base/languages/gettext/';
        $lotto_platform_translations_directory = $wp_content_path . '/plugins/lotto-platform/languages/gettext/';
        $validation_translations_directory = APPPATH . '/lang/';

        if ($this->is_directory_exists($base_translations_directory)) {
            $locales = array_filter(scandir($base_translations_directory), function ($path) {
                return !in_array($path, ['.', '..']);
            });
        }

        if ($this->is_directory_exists($lotto_platform_translations_directory)) {
            //
        }

        if ($this->is_directory_exists($validation_translations_directory)) {
            //
        }

        // Allow user to preselect which locales you want to merge
        if (!empty($_GET['locales'])) {
            $locales = array_filter(explode(',', $_GET['locales']), function ($item) use ($locales) {
                return in_array(trim($item), $locales);
            });
        }

        $file_overwrite = isset($_GET['overwrite']);

        $merge_base = true;
        $merge_validation = true;
        if (isset($_GET['merge'])) {
            if ($_GET['merge'] == 'base') {
                $merge_validation = false;
            }
            if ($_GET['merge'] == 'validation') {
                $merge_base = false;
            }
        }

        $updated_locales = [];
        foreach ($locales as $locale) {
            $locale = trim($locale);
            $filePath = $base_translations_directory . $locale . '/LC_MESSAGES/base-theme.po';

            if ($merge_base && file_exists($filePath)) {
                $base_translations = $this->get_translations_from_file($filePath);
            } else {
                $base_translations = [];
            }

            $locale_parts = explode('_', $locale);

            $filePath = $validation_translations_directory . $locale_parts[0] . '/validation.po';

            if ($merge_validation && file_exists($filePath)) {
                $validation_translations = $this->get_translations_from_file($filePath);
            } else {
                $validation_translations = [];
            }

            // Get target file
            $file = $lotto_platform_translations_directory . $locale . '/LC_MESSAGES/lotto-platform.po';
            if (!file_exists($file)) {
                // Create translation file if it's not exists
                if (!fopen($file, 'w')) {
                    throw new \Exception('Could not crate file "' . $file . '"');
                }
            }
            $new_file_content_array = $this->get_translations_from_file($file);

            // Remove translations which already exists in main translations file
            $base_translations = $this->remove_duplications_from_translations_array(
                $new_file_content_array,
                $base_translations
            );
            $validation_translations = $this->remove_duplications_from_translations_array(
                $new_file_content_array,
                $validation_translations
            );

            // Add initial metadata
            $metadata = [
                [
                    'key' => 'msgid ""',
                    'translation' => 'msgstr ""
"Project-Id-Version: WhiteLotto - Lotto Platform plugin\n"
"Language: ' . $locale . '\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"X-Poedit-SourceCharset: UTF-8\n"'
                ]
            ];

            $new_file_content_array = array_merge(
                $metadata,
                $new_file_content_array,
                $base_translations,
                $validation_translations
            );
            $new_file_content_array = array_map(function ($item) {
                $output = isset($item['context']) ? $item['context'] . PHP_EOL : '';
                $output .= $item['key'] . PHP_EOL . $item['translation'] . PHP_EOL;

                return $output;
            }, $new_file_content_array);

            if ($file_overwrite) {
                $filename = $file;
            } else {
                $filename = str_replace('lotto-platform.po', 'lotto-platform.new.po', $file);
            }

            file_put_contents(
                $filename,
                implode(PHP_EOL, $new_file_content_array)
            );

            $updated_locales[] = $locale;
        }

        echo 'Done.<br/>';
        echo 'Updated locales (' . count($updated_locales) . '): ' . implode(', ', $updated_locales);
    }

    /**
     * @param string $path
     *
     * @return bool
     * @throws Exception
     */
    private function is_directory_exists(string $path)
    {
        if (!file_exists($path)) {
            throw new \Exception('Directory "' . $path . '" does not exists.');
        }

        return true;
    }

    /**
     * Use only with .po files
     *
     * @param string $file_path
     *
     * @return array
     */
    private function get_translations_from_file(string $file_path): array
    {
        $file_content = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $key = 0;
        $translations = [];
        $original_started = false;
        $translation_started = false;

        // Load translations to memory
        // $lineNum 0 and 1 are metadata, we don't want to import this
        foreach ($file_content as $line_num => $line) {
            if (strpos($line, 'msgctxt') === 0) {
                $translations[$key + 1]['context'] = $line;
            } elseif (strpos($line, 'msgid') === 0 && $line_num !== 0) {
                $key++;
                // New trans key found
                $translations[$key]['key'] = $line;

                $original_started = true;
            } elseif (strpos($line, '"') === 0 && $original_started) {
                // Continue key
                $translations[$key]['key'] .= PHP_EOL . $line;
            } elseif (strpos($line, 'msgstr') === 0 && $line_num !== 1) {
                // New translation found
                $original_started = false;
                $translation_started = true;

                $translations[$key]['translation'] = $line;
            } elseif (strpos($line, '"') === 0 && $translation_started) {
                // Continue translation
                $translations[$key]['translation'] .= PHP_EOL . $line;
            }
        }

        unset($file_content); // free some memory

        return $translations;
    }

    /**
     * @param array $original_translations
     * @param array $new_translations
     *
     * @return array
     */
    private function remove_duplications_from_translations_array(array $original_translations, array $new_translations): array
    {
        foreach ($original_translations as $index => $item) {
            $duplication_found = false;
            foreach ($new_translations as $id => $translation) {
                if ($duplication_found) {
                    continue;
                }

                if (
                    isset($original_translations[$index]['key']) &&
                    $translation['key'] === $original_translations[$index]['key']
                ) {
                    if (
                        isset($original_translations[$index]['context']) &&
                        isset($translation['key']['context'])
                    ) {
                        if ($translation['key']['context'] === $original_translations[$index]['context']) {
                            unset($new_translations[$id]);
                            $duplication_found = true;
                        }
                    } else {
                        unset($new_translations[$id]);
                        $duplication_found = true;
                    }
                }
            }
        }

        return $new_translations;
    }

    /**
     * Use this task to generate po files from validation.php files
     */
    public function action_generate_validation_po_files()
    {
        $validation_files_path = APPPATH . 'lang/';
        $translations_paths = array_filter(scandir($validation_files_path), function ($path) {
            return !in_array($path, ['.', '..', 'gettext']);
        });

        $created_files = [];
        $updated_files = [];
        foreach ($translations_paths as $lang) {
            $file = $validation_files_path . $lang . '/validation.php';

            if (!file_exists($file)) {
                continue;
            }

            $translations = include_once $file;

            $file_content = 'msgid ""
msgstr ""
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"X-Poedit-SourceCharset: UTF-8\n"' . PHP_EOL . PHP_EOL;
            if (is_array($translations)) {
                foreach ($translations as $key => $value) {
                    $file_content .= 'msgid "' . $key . '"' . PHP_EOL;
                    $file_content .= 'msgstr "' . $value . '"' . PHP_EOL;
                    $file_content .= PHP_EOL;
                }
            }

            if (file_exists($validation_files_path . $lang . '/validation.po')) {
                $updated_files[] = $lang;
            } else {
                $created_files[] = $lang;
            }
            file_put_contents($validation_files_path . $lang . '/validation.po', $file_content);
        }

        echo 'Done.<br/>';
        echo 'Created PO files (' . count($created_files) . '): ' . implode(', ', $created_files) . '<br/>';
        echo 'Updated PO files (' . count($updated_files) . '): ' . implode(', ', $updated_files) . '<br/>';
    }

    public function action_checkcountryflags()
    {
        $countries = Lotto_Helper::get_localized_country_list();
        $flags = [];

        $dir = new DirectoryIterator(APPPATH . '../../../dev/wordpress/flags');
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                $nm = $fileinfo->getFilename();
                $nm = explode('.', $nm);
                $nm = $nm[0];
                $flags[] = $nm;
            }
        }

        foreach ($countries as $country => $countryname) {
            if (!in_array(strtolower($country), $flags)) {
                echo $country . ' ' . $countryname . ' not found<br>';
            }
        }

        foreach ($flags as $flag) {
            if (!in_array(strtoupper($flag), array_keys($countries))) {
                echo $flag . '<br>';
            }
        }
    }

    public function sort_by_language($a, $b)
    {
        $cmp = strcmp($a[3], $b[3]);
        if ($cmp == 0) {
            // the second level of sort needs to be locale-dependend (sort by name of subdivision) to prevent displaying e.g. Łódzkie in the end
            $collator = new Collator($a[3]);
            $collator->setAttribute(Collator::NUMERIC_COLLATION, Collator::ON); // sort e.g. Praha 2 before Praha 10
            return $collator->compare($a[2], $b[2]);
        }
        return $cmp;
    }

    public function action_usdjackpots()
    {
        $currencies = Helpers_Currency::getCurrencies();
        $currency_map = [];
        foreach ($currencies as $item) {
            $currency_map[$item['code']] = $item;
        }

        $lotteries = Model_Lottery::get_all_lotteries();
        foreach ($lotteries['__by_id'] as $lottery) {
            $dblottery = Model_Lottery::find_by_pk($lottery['id']);

            $currency_rate = $currency_map[$lottery['currency']]['rate'];
            $multiplier_usd = round(1 / $currency_rate, 4);
            $jackpot_formatted = round(
                $lottery['current_jackpot'] * $multiplier_usd,
                2
            );

            $dblottery->set([
                'current_jackpot_usd' => $jackpot_formatted
            ]);
            $dblottery->save();
        }
    }

    public function action_newcurrencies()
    {
        set_time_limit(0);
        libxml_use_internal_errors(true);

        Lotto_Helper::clear_cache("model_currency");

        $countries = Lotto_Helper::get_localized_country_list();
        $currencies = [];
        foreach ($countries as $code => $country) {
            $currencies[Helpers_Currency::get_currency_for_country($code)] = 1;
        }

        foreach ($currencies as $currency => $notimportant) {
            try {
                $doc = new DOMDocument();
                $doc->loadHTML(Lotto_Helper::load_HTML_url("https://finance.google.com/finance/converter?a=1&from=USD&to=" . $currency));
                $xpath = new DOMXPath($doc);

                $query = '//div[contains(@id, "currency_converter_result")]/span[contains(@class, "bld")]';
                $value = $xpath->query($query);
                if ($value->length != 1) {
                    throw new Exception('Rate bad length.');
                }
                $value = trim($value->item(0)->nodeValue);

                if (preg_match('/^[0-9.]+/u', $value, $m)) {
                    $value = $m[0];
                    if (!is_numeric($value)) {
                        throw new Exception('Rate isn\'t numeric');
                    }

                    echo $currency . ': ' . $value . '<br>';
                    $res = Model_Currency::find_by_code($currency);
                    if ($res !== false && count($res)) {
                        $res = $res[0];
                        $res->set(["rate" => $value]);
                        $res->save();
                    } else {
                        $res = Model_Currency::forge();
                        $res->set(["code" => $currency, "rate" => $value]);
                        $res->save();
                    }
                } else {
                    echo 'Rate bad value.' . $currency . '<br>';
                }
            } catch (Exception $e) {
                echo 'Exception Rate bad value.' . $currency . ': ' . $e->getMessage() . '<br>';
            }
        }
        Lotto_Helper::clear_cache("model_currency");
    }

    public function action_fixpaymentorders()
    {
        $whitelabels = Model_Whitelabel::find_all();
        foreach ($whitelabels as $whitelabel) {
            $whitelabel_payment_methods_without_currency = Model_Whitelabel_Payment_Method::get_payment_methods_for_whitelabel($whitelabel);
            $lang_methods = [];
            foreach ($whitelabel_payment_methods_without_currency as $method) {
                $lang_methods[$method['language_id']][] = $method;
            }
            foreach ($lang_methods as $lang) {
                for ($i = 1; $i <= count($lang); $i++) {
                    $method = Model_Whitelabel_Payment_Method::find_by_pk($lang[$i - 1]['id']);
                    $method->set(["order" => $i]);
                    $method->save();
                }
            }
        }
        Lotto_Helper::clear_cache(["model_whitelabel_payment_method"]);
    }

    public function action_checkemails()
    {
        $bademails = file(APPPATH . 'vendor/bademails/list.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $users = Model_Whitelabel_User::find_all();
        foreach ($users as $user) {
            $email = explode("@", $user['email']);
            $domain = $email[1];
            foreach ($bademails as $bademail) {
                if (substr($domain, -strlen($bademail)) == $bademail) {
                    echo $user['email'] . ' - ' . $user['last_active'] . '<br>';
                }
            }
        }
    }

    public function action_geoip()
    {
        $users = Model_Whitelabel_User::find_all();
        foreach ($users as $user) {
            $geoip = Lotto_Helper::get_geo_IP_record($user->last_ip);
            if ($geoip !== false && !empty($geoip->country->isoCode)) {
                $country = $geoip->country->isoCode;
                $user->set(["last_country" => $country]);
                $user->save();
                echo "Set up last_country for user " . $user['email'] . " [" . $user->last_ip . "]: " . $country . "<br>";
            } else {
                echo "Error for last_country: " . $user['email'] . " [" . $user->last_ip . "]<br>";
            }
            $geoip = Lotto_Helper::get_geo_IP_record($user->register_ip);
            if ($geoip !== false) {
                $country = $geoip->country->isoCode;
                $user->set(["register_country" => $country]);
                $user->save();
                echo "Set up register_country for user " . $user['email'] . " [" . $user->register_ip . "]: " . $country . "<br>";
            } else {
                echo "Error for register_country: " . $user['email'] . " [" . $user->register_ip . "]<br>";
            }
        }
    }

    public function action_checkusersubdivision()
    {
        $users = Model_Whitelabel_User::find_all();

        $subdivisions = json_decode(file_get_contents(APPPATH . 'vendor/iso/subdivisions.json'), true);

        foreach ($users as $user) {
            if (empty($user['state'])) {
                continue;
            }
            if (!isset($subdivisions[$user['state']])) {
                echo $user['id'] . '<br>';
            }
        }
    }

    public function action_subdivisions()
    {
        $data = json_decode(file_get_contents(APPPATH . 'vendor/iso/scrap/subdivisions.json'));

        // array of all subtypes
        $subtypes = [];

        // will keep all languages of subtypes for each country (appearing in table, not in the list of subtypes)
        $slanguages = [];

        // will keep all subtypes in every language from $slanguage
        $lsubtypes = [];

        foreach ($data as &$item) {
            foreach ($item[2] as $key => &$subdivision) {
                $subdivision[0] = preg_replace('/ \(20.+?\)/u', "", $subdivision[0]); // remove (20 inhabitet islands)
                $subdivision[1] = preg_replace('/\*/u', "", $subdivision[1]); // remove *
                $subdivision[2] = preg_replace('/\*/u', "", $subdivision[2]); // remove *
                $subdivision[5] = preg_replace('/( \[.*\]|\*)/u', "", $subdivision[5]); // remove * and [...]

                $m = [];
                if (preg_match('/(.*?) \[(.+?) ?([A-Z-]+)?\]$/u', $subdivision[2], $m)) { // if there is some text inside []
                    $newname = $m[1];
                    if (!preg_match('/^([A-Z0-9-]+|city)$/u', $m[2])) { // remove the XX-XXX code or 'city' string
                        if (strcmp($m[1], $m[2]) != 0) { // if there are equal, ommit one
                            $newname .= '; ' . $m[2];
                        }
                    }
                    $subdivision[2] = $newname;
                }
                if (preg_match('/(.*?) \(see also(.+)\)$/u', $subdivision[2], $m)) { // remove the "see also..." strings
                    $subdivision[2] = $m[1];
                }
                if (!empty($subdivision[5])) {
                    $subdivision[2] .= ' (' . $subdivision[5] . ')';
                }

                //$subdivision[0] = ucfirst($subdivision[0]); // capitalize first letters
                if (!in_array($subdivision[0], $subtypes)) { // make array of subtypes
                    $subtypes[] = $subdivision[0];
                }

                if (!isset($slanguages[$item[0]])) {
                    $slanguages[$item[0]] = [];
                }
                if (!in_array($subdivision[3], $slanguages[$item[0]])) {
                    $slanguages[$item[0]][] = $subdivision[3];
                }
            }
        }


        unset($item);

        // second pass for subdivision names
        // create array of subdivision translations
        foreach ($data as $item) {
            foreach ($item[1] as $key => $subnames) {
                $eng_name = null;
                $tmparray = [];
                foreach ($subnames as $subname) {
                    $m = [];
                    if (preg_match("/(.*) \((.*)\)$/u", $subname, $m)) {
                        $m[1] = preg_replace('/ \(20.+?\)/u', "", $m[1]);
                        if ($m[2] == 'en') {
                            $eng_name = $m[1];
                        }

                        $tmparray[$m[2]] = $m[1];
                    }
                }

                // remove unused subdivision translations (e.g. french for polish names)
                $ltmparray = $tmparray;
                $ltmparraynew = [];
                foreach ($ltmparray as $langkey => $name) {
                    // remove duplicates
                    if (in_array($langkey, $slanguages[$item[0]]) && !in_array($name, $ltmparraynew)) {
                        $ltmparraynew[] = $name;
                    }
                }
                if (empty($ltmparraynew)) {
                    // in case iso did not provide name in local languages, use all of provided
                    $ltmparraynew = $ltmparray;
                }
                $lsubtypes[$item[0]][$tmparray['en']] = $ltmparraynew;
            }
        }


        // ok, we've got data quite good filtered, lets now sort it by language to properly connect the subdivision localized names
        foreach ($data as &$item) {
            usort($item[2], [$this, "sort_by_language"]);
        }
        unset($item);

        // ok, let's finally create flat array for our data
        // so we've got: flat_data, last_updated and lsubtypes tables finally
        $flat_data = [];
        $last_updated = [];

        foreach ($data as $item) {
            // 2-letter country, english name, iso-code, name, language, parent
            foreach ($item[2] as $subdivision) {
                // set also last updated table for reference
                if (!isset($last_updated[$item[0]])) {
                    $last_updated[$item[0]] = $item[3];
                }
                $flat_data[] = [$item[0], $subdivision[0], $subdivision[1], [$subdivision[2]], [$subdivision[3]], $subdivision[4]];
            }
        }

        rsort($last_updated);

        // ok, now we've got flat table so it's easier to combine the duplicates
        $byiso = [];
        foreach ($flat_data as $item) {
            if (isset($byiso[$item[2]])) {
                if (!in_array($item[3][0], $byiso[$item[2]][3])) {
                    $byiso[$item[2]][3][] = $item[3][0];
                }

                $byiso[$item[2]][4][] = $item[4][0];
                continue;
            }
            $byiso[$item[2]] = $item;
        }


        // let's create final table fucking table!!! CRAZY!!! GOT MY MIND BLOWN!

        $final = [];
        foreach ($byiso as $item) {
            $final[$item[2]] = [$item[0], implode('; ', $lsubtypes[$item[0]][$item[1]]), implode('; ', $item[3]), $item[5], 0];
        }

        // update children count for faster usage
        foreach ($final as $item) {
            if (!empty($item[3])) {
                $final[$item[3]][4]++;
            }
        }

        // save the newest iso date
        file_put_contents(APPPATH . 'vendor/iso/lastupdate.json', $last_updated[0]);

        file_put_contents(APPPATH . 'vendor/iso/subdivisions.json', json_encode($final, JSON_UNESCAPED_UNICODE));

        echo 'OK';
    }

    public function action_generateusertokens()
    {
        $users = Model_Whitelabel_User::find_all();
        foreach ($users as $user) {
            $unique_token = Lotto_Security::generate_user_token($user['whitelabel_id']);
            $user->set(["token" => $unique_token]);
            $user->save();
        }
    }

    public function action_generatetransactiontokens()
    {
        $transactions = Model_Whitelabel_Transaction::find_all();
        foreach ($transactions as $transaction) {
            $unique_token = Lotto_Security::generate_transaction_token($transaction['whitelabel_id']);
            $transaction->set(["token" => $unique_token]);
            $transaction->save();
        }
    }

    public function action_generatetickettokens()
    {
        $tickets = Model_Whitelabel_User_Ticket::find_all();
        foreach ($tickets as $ticket) {
            $unique_token = Lotto_Security::generate_ticket_token($ticket['whitelabel_id']);
            $ticket->set(["token" => $unique_token]);
            $ticket->save();
        }
    }

    public function action_generatewithdrawaltokens()
    {
        $withdrawals = Model_Withdrawal_Request::find_all();
        foreach ($withdrawals as $withdrawal) {
            $unique_token = Lotto_Security::generate_withdrawal_token($withdrawal['whitelabel_id']);
            $withdrawal->set(["token" => $unique_token]);
            $withdrawal->save();
        }
    }

    public function action_calclottorisq2()
    {
        set_time_limit(0);
        echo '<!DOCTYPE html>' . "\n";
        echo '<html><head>' . "\n";
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.js" type="text/javascript"></script>' . "\n";
        echo '</head><body>' . "\n";

        $lotteries = Model_Lottery::get_all_lotteries();
        $currencies = Helpers_Currency::getCurrencies();
        $kcurrencies = [];
        foreach ($currencies as $currency) {
            $kcurrencies[$currency['code']] = $currency;
        }
        $multiple = 1.9;
        $l = 0;
        $simulations = [/* 100, 1000, 5000, */
            10000/* , 25000, 50000, 100000, 500000, 1000000, 10000000, 100000000, 1000000000 */];
        $jackpots = [1, 2, 5, 10, 12.5, 13, 14, 15, 16, 17, 17.5, 18, 19, 20, 21, 22, 23, 24, 25, 30, 40, 41, 42, 43, 44, 45, 47, 48, 49, 50, 53, 54, 55, 57, 58, 59, 60, 61, 69, 70, 71, 72, 73, 74, 75, 80, 82, 83, 84, 85, 90, 95, 100, 105, 107, 110, 112, 113, 114, 115, 118, 119, 120, 121, 122, 123, 124, 125, 126, 127, 128, 129, 130, 135, 137, 138, 139, 140, 150, 160/* , 250, 300, 350, 400 */];
        // powerball, megamillions, eurojackpot, superenalotto, uklottery, euromillions, lotto pl
        $ticket_prices = [2.15, 1.10, 2.00, 1.15, 2.20, 2.2, 3.45];
        $ticket_currencies = ["USD", "USD", "EUR", "EUR", "GBP", "EUR", "PLN"];
        $ticket_orig_sell_prices = [2, 1, 2, 1, 2, 2.5, 3];

        $ticket_sell_prices = [];
        foreach ($ticket_prices as $key => $price) {
            $ticket_sell_prices[] = $price + 1;
            $currency_rate = $kcurrencies[$ticket_currencies[$key]]['rate'];
            $multiplier_usd = round(1 / $currency_rate, 4);
            $sell_price = round($ticket_sell_prices[$key] * $multiplier_usd, 4);
            $ticket_sell_prices[$key] = $sell_price;
        }
        foreach ($ticket_sell_prices as $key => $price) {
            $currency_eur_rate = $kcurrencies["EUR"]['rate'];
            $sell_price = round($price * $currency_eur_rate, 1);
            $ticket_sell_prices[$key] = $sell_price;
        }
        //var_dump($ticket_sell_prices);
        //exit();
        //$ticket_sell_prices = array(2.9, 2.0, 3.0, 2.0, 3.5, 3.7, 0.96);

        $warnings = [];

        $chartinfo = [];
        $chartdata = [];

        $eurojackpot = [9.50, 10.80, 18.60, 22.70, 23.70, 76.8, 135.6, 284.5, 5654.1, 144180.8, 817025.0, 0.0];
        $pllotto = [24.0, 150.1, 4362.3, 0.0];

        foreach ($simulations as $simid => $simitem) {
            echo '<h1>Simulation for ' . $simitem . ' purchases</h1>';
            $simulate = $simitem;
            $max_ins_tier = 1;
            $total_wins_cost = 0;
            foreach ($lotteries['__by_id'] as $lottery) {
                if ($lottery['id'] != 5) {
                    continue;
                }
                echo "<h2>Lottery name: " . $lottery['name'] . '</h2>';
                $type = Model_Lottery_Type_Data::get_lottery_type_data($lottery);

                for ($i = 0; $i < count($type); $i++) {
                    $max_ins_tier = $i + 1;
                    if ($max_ins_tier != 2) {
                        continue;
                    }

                    foreach ($jackpots as $jackpot) {
                        $l++;
                        echo "<b>Simulation #" . ($l) . "</b><br>";
                        echo "Multiple: 1.9<br>";
                        echo "Jackpot: " . Lotto_View::format_currency($jackpot * 1000000, $currencies[$lottery['currency_id']]['code']) . '<br>';
                        echo "Maximum insurance tier: " . $max_ins_tier . '<br>';
                        echo "Purchases count: " . $simulate . '<br>';
                        //echo "Ticket sell price: ".Lotto_View::format_currency($ticket_sell_prices[$lottery['id']-1], $currencies[$lottery['currency_id']]['code'], true).'<br>';
                        //echo "Ticket sell income: ".Lotto_View::format_currency($ticket_sell_prices[$lottery['id']-1]*$simulate, $currencies[$lottery['currency_id']]['code'], true).'<br>';

                        $tickets_cost = $simulate * $ticket_prices[$lottery['id'] - 1];
                        $income = $simulate * $ticket_sell_prices[$lottery['id'] - 1];
                        $income_orig = $simulate * $ticket_orig_sell_prices[$lottery['id'] - 1];

                        echo "Ticket cost: " . Lotto_View::format_currency($ticket_prices[$lottery['id'] - 1], $currencies[$lottery['currency_id']]['code'], true) . "<br>";
                        echo "Tickets cost: " . Lotto_View::format_currency($tickets_cost, $currencies[$lottery['currency_id']]['code'], true) . "<br>";

                        $total_ins_price = 0.0;
                        $total_total_wins_cost = 0.0;
                        $total_wins_cost = 0;

                        foreach ($type as $key => $item) {
                            $odds = $item['odds'];
                            $prize = $item['is_jackpot'] ? $jackpot * 1000000 : $item['prize'];
                            $wins = 0;
                            if ($item['type'] == 1 && !$item['is_jackpot'] && !empty($item['estimated'])) {
                                $prize = $item['estimated'];
                            } elseif ($item['type'] == 2) {
                                // uk lottery quick pick
                                $prize = 0;
                            } elseif ($item['type'] == 1 && !$item['is_jackpot'] && empty($item['estimated'])) {
                                if ($lottery['id'] == 3) {
                                    $prize = $eurojackpot[count($eurojackpot) - $key - 1];
                                } elseif ($lottery['id'] == 7) {
                                    $prize = $pllotto[count($pllotto) - $key - 1];
                                }
                            }
                            echo "Odds for this tier: 1/" . $odds . '<br>';
                            if ($key < $max_ins_tier) {
                                $ins_price = round($prize * (1.0 / $odds) * $multiple, 2);
                                echo "Insurance price for " . ($key + 1) . "-tier: " . Lotto_View::format_currency($ins_price, $currencies[$lottery['currency_id']]['code'], true) . "<br>";
                                $total_ins_price += $ins_price;
                            } else {
                                $wins = round((float)($simulate) / $odds);
                                if ($item['is_jackpot']) {
                                    $wins = $jackpot * 1000000;
                                }
                                $total_wins_cost = $wins * round($prize, 2);
                                $total_total_wins_cost += $total_wins_cost;
                            }
                            echo "Prize: " . Lotto_View::format_currency($prize, $currencies[$lottery['currency_id']]['code'], true) . "<br>";
                            echo "Wins: " . $wins . "<br>";
                            echo "Total wins costs for this tier: " . Lotto_View::format_currency($total_wins_cost, $currencies[$lottery['currency_id']]['code'], true) . "<br>";
                            echo "<br>";
                            //echo "Insurance price: ".Lotto_View::format_currency($ins_price, $currencies[$lottery['currency_id']]['code'], true)."<br>";
                        }
                        $total_ins_cost = $simulate * $total_ins_price;
//                        if ($income - $total_total_wins_cost - $total_ins_cost <= 0) {
//                            if ($income_orig - $total_total_wins_cost - $total_ins_cost < 0) {
//                                if ($total_total_wins_cost + $total_ins_cost - $income_orig >= $tickets_cost - $income) {
//                                    $warnings[] = array($lottery, $jackpot, $max_ins_tier, $simulate, $total_ins_cost + $total_total_wins_cost - $tickets_cost);
//                                    echo '<span style="color: red;">';
//                                }
//                            }
//                        }

                        echo "Total insurance cost: " . Lotto_View::format_currency($total_ins_cost, $currencies[$lottery['currency_id']]['code'], true) . "<br>";
                        echo "Total wins cost: " . Lotto_View::format_currency($total_total_wins_cost, $currencies[$lottery['currency_id']]['code'], true) . "<br>";

                        echo "Total cost: " . Lotto_View::format_currency($total_ins_cost + $total_total_wins_cost, $currencies[$lottery['currency_id']]['code'], true) . "<br>";
                        echo "Cost of one ticket: " . Lotto_View::format_currency(($total_ins_cost + $total_total_wins_cost) / $simulate, $currencies[$lottery['currency_id']]['code'], true) . "<br>";

                        $chartdata[$jackpot] = round(($total_ins_cost + $total_total_wins_cost) / $simulate, 2);

                        //echo "Total income: ".Lotto_View::format_currency($income-$total_ins_cost-$total_total_wins_cost, $currencies[$lottery['currency_id']]['code'], true).'<br><br>';
                        //if ($income-$total_total_wins_cost-$total_ins_cost <= 0)
                        {
                            //echo '</span>';
                        }
                    }
                }
            }
        }
    }

    public function action_findfreetickets()
    {
        $date_from_string = "Tue, 21 Aug 2018 20:08:49 +0000";
        $date_to_string = "27 Aug 2018 11:11:41 +0000";
        $date_from = date("Y-m-d H:i:s", strtotime($date_from_string));
        $date_to = date("Y-m-d H:i:s", strtotime($date_to_string));
        $total_amount = 0;
        $total_cost = 0;
        $total_tickets = 0;
        $pending = 0;
        $pending_cost = 0;
        $won = 0;
        $won_prize = 0;
//        try {
        $db = DB::query("SELECT whitelabel_user_ticket.*, whitelabel_transaction.whitelabel_payment_method_id FROM whitelabel_user_ticket, whitelabel_transaction WHERE whitelabel_user_ticket.date >= '$date_from' AND whitelabel_user_ticket.date <= '$date_to' AND whitelabel_user_ticket.whitelabel_transaction_id = whitelabel_transaction.id AND whitelabel_transaction.whitelabel_payment_method_id = 1");

        $res = $db->execute();
        if (is_null($res)) {
            return -1;
        }
        if (empty($res[0])) {
            return -1;
        }
        echo "<table  border=\"1\" style=\"text-align: center;\">
<tr>
    <th width='50'>User ID</th>
    <th width='150'>Ticket bought count</th>
    <th width='150'>Ticket cost</th>
    <th width='150'>Ticket amount</th>
    <th width='150'>User balance</th>
    <th width='150'>User balance after substract</th>
    <th width='150'>Whitelabel prefix</th>
    <th width='200'>email</th>
 </tr>
 ";
        foreach ($res as $ticket) {
            $userTickets[$ticket['whitelabel_user_id']][] = $ticket;
            if ($ticket['date_processed'] != null) {
                $pending++;
                $pending_cost = $ticket['cost'];
            }
            if ($ticket['status'] == 1) {
                $won++;
                $won_prize = $ticket['prize_usd'];
            }
        }
        if (isset($userTickets)) {
            foreach ($userTickets as $ticket) {
                $user = Model_Whitelabel_User::find_by_pk($ticket[0]['whitelabel_user_id']);
                $whitelabel = Model_Whitelabel::find_by_pk($ticket[0]['whitelabel_id']);
                $ticket_count = count($ticket);
                $ticket_amount = 0;
                $ticket_cost = 0;
                foreach ($ticket as $t) {
                    $ticket_amount += $t['amount'];
                    $ticket_cost += $t['cost'];
                }
                $balance_after = $user->balance - $ticket_amount;
                $bg = '';
                if ($balance_after < 0) {
                    $bg = 'red';
                }
                echo "<tr style='background-color: {$bg}'>";
                echo "<td>$user->id</td>";
                echo "<td>$ticket_count</td>";
                echo "<td>$ticket_cost$</td>";
                echo "<td>$ticket_amount$</td>";
                echo "<td>$user->balance$</td>";
                echo "<td>$balance_after$</td>";
                echo "<td >{$whitelabel->prefix}</td>";
                echo "<td>$user->email</td>";
                echo "</tr>";
                $total_amount += $ticket_amount;
                $total_cost += $ticket_cost;
            }
        }
        echo "</table>";
        echo "Ticket bought: " . count($res);
        echo "<br>Total cost: $total_cost$";
        echo "<br>Total amount: $total_amount$";
        echo "<br>Pending: $pending";
        echo "<br>Pending cost: $pending_cost";
        echo "<br>Won: $won";
        echo "<br>Won prize: $won_prize";
//        } catch (Exception $e) {
//            die("error");
//        }
    }

    public function action_calclottorisq()
    {
        echo '<!DOCTYPE html>' . "\n";
        echo '<html><head>' . "\n";
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.js" type="text/javascript"></script>' . "\n";
        echo '</head><body>' . "\n";

        $lotteries = Model_Lottery::get_all_lotteries();
        $currencies = Helpers_Currency::getCurrencies();
        $multiple = 1.9;
        $l = 0;
        $simulations = [100, 1000, 5000, 10000, 50000, 100000, 500000, 1000000, 10000000, 100000000, 1000000000];
        $jackpots = [1, 2, 10, 12.5, 13, 14, 15, 16, 17, 17.5, 18, 19, 20, 21, 22, 23, 24, 25, 30, 40, 41, 42, 43, 44, 45, 47, 50, 53, 55, 60/* , 70, 71, 72, 73, 74, 75, 80, 82, 83, 84, 85, 90, 95, 100, 110, 112, 113, 114, 115, 118, 119, 120, 121, 122, 123, 124, 125, 126, 127, 128, 129, 130, 135, 137, 138, 139, 140, 150, 200, 250, 300, 350, 400 */];
        $ticket_prices = [2.15, 1.10, 2.00, 1.15, 2.20, 2.2, 3.45];
        $ticket_orig_sell_prices = [2, 1, 2, 1, 2, 2.5, 4];
        $ticket_sell_prices = [2.9, 1.9, 3.0, 2.2, 2.5, 3.2, 0.96];

        $warnings = [];

        $chartinfo = [];
        $chartdata = [];

        $eurojackpot = [8.30, 10.60, 14.30, 18.60, 22.50, 60.7, 104.4, 260.3, 5519.2, 143501.7, 304941.1, 0.0];
        $pllotto = [24.0, 86.9, 4530.0, 0.0];

        foreach ($simulations as $simid => $simitem) {
            //echo '<h1>Simulation for '.$simitem.' purchases</h1>';
            $simulate = $simitem;
            $max_ins_tier = 1;
            $total_wins_cost = 0;
            foreach ($lotteries['__by_id'] as $lottery) {
                if ($lottery['id'] != 7) {
                    continue;
                }
                //echo "<h2>Lottery name: ".$lottery['name'].'</h2>';
                $type = Model_Lottery_Type_Data::get_lottery_type_data($lottery);

                for ($i = 0; $i < count($type); $i++) {
                    $max_ins_tier = $i + 1;
                    if ($max_ins_tier != 1) {
                        continue;
                    }

                    foreach ($jackpots as $jackpot) {
                        $l++;
                        //echo "<b>Simulation #".($l)."</b><br>";
                        //echo "Multiple: 1.9<br>";
                        //echo "Jackpot: ".Lotto_View::format_currency($jackpot*1000000, $currencies[$lottery['currency_id']]['code']).'<br>';
                        //echo "Maximum insurance tier: ".$max_ins_tier.'<br>';
                        //echo "Purchases count: ".$simulate.'<br>';
                        //echo "Ticket sell price: ".Lotto_View::format_currency($ticket_sell_prices[$lottery['id']-1], $currencies[$lottery['currency_id']]['code'], true).'<br>';
                        //echo "Ticket sell income: ".Lotto_View::format_currency($ticket_sell_prices[$lottery['id']-1]*$simulate, $currencies[$lottery['currency_id']]['code'], true).'<br>';

                        $tickets_cost = $simulate * $ticket_prices[$lottery['id'] - 1];
                        $income = $simulate * $ticket_sell_prices[$lottery['id'] - 1];
                        $income_orig = $simulate * $ticket_orig_sell_prices[$lottery['id'] - 1];
                        $total_ins_price = 0.0;
                        $total_total_wins_cost = 0.0;
                        $total_wins_cost = 0;

                        foreach ($type as $key => $item) {
                            $odds = $item['odds'];
                            $prize = $item['is_jackpot'] ? $jackpot * 1000000 : $item['prize'];
                            $wins = null;
                            if ($item['type'] == 1 && !$item['is_jackpot'] && !empty($item['estimated'])) {
                                $prize = $item['estimated'];
                            } elseif ($item['type'] == 2) {
                                // uk lottery quick pick
                                $prize = 2.20;
                            } elseif ($item['type'] == 1 && !$item['is_jackpot'] && empty($item['estimated'])) {
                                if ($lottery['id'] == 3) {
                                    $prize = $eurojackpot[count($eurojackpot) - $key - 1];
                                } elseif ($lottery['id'] == 7) {
                                    $prize = $pllotto[count($pllotto) - $key - 1];
                                }
                            }
                            if ($key < $max_ins_tier) {
                                $ins_price = round($prize * (1.0 / $odds) * $multiple, 2);
                                $total_ins_price += $ins_price;
                            } else {
                                $wins = floor((float)$simulate / $odds);
                                if ($item['is_jackpot']) {
                                    $wins = $jackpot * 1000000;
                                }
                                $total_wins_cost = $wins * round($prize, 2);
                                $total_total_wins_cost += $total_wins_cost;
                            }
                            //echo "Prize: ".$prize."<br>";
                            if (!empty($wins)) {
                                //echo "Win count: ".$wins."<br>";
                            }
                            //echo "Wins: ".$wins."<br>";
                            //echo "Insurance price: ".Lotto_View::format_currency($ins_price, $currencies[$lottery['currency_id']]['code'], true)."<br>";
                        }
                        $total_ins_cost = $simulate * $total_ins_price;

                        //if ($income-$total_total_wins_cost-$total_ins_cost <= 0)
                        //if ($income_orig-$total_total_wins_cost-$total_ins_cost < 0)
                        if ($total_total_wins_cost + $total_ins_cost - $income_orig >= $tickets_cost - $income) {
                            $warnings[] = [$lottery, $jackpot, $max_ins_tier, $simulate, $total_ins_cost + $total_total_wins_cost - $tickets_cost];
                            $chartdata[$lottery['id'] . '_' . $max_ins_tier][$jackpot][] = round($total_ins_cost + $total_total_wins_cost - $tickets_cost, 2);

                            //echo '<span style="color: red;">';
                        }

                        //echo "Total insurance cost: ".Lotto_View::format_currency($total_ins_cost, $currencies[$lottery['currency_id']]['code'], true)."<br>";
                        //echo "Total wins cost: ".Lotto_View::format_currency($total_total_wins_cost, $currencies[$lottery['currency_id']]['code'], true)."<br>";
                        //echo "Total income: ".Lotto_View::format_currency($income-$total_ins_cost-$total_total_wins_cost, $currencies[$lottery['currency_id']]['code'], true).'<br><br>';
                        //if ($income-$total_total_wins_cost-$total_ins_cost <= 0)
                        {
                            //echo '</span>';
                        }
                    }
                }
            }
        }

        foreach ($chartdata as $chartdesc => $chartitems) {
            $chartdescex = explode("_", $chartdesc);
            echo '<h1>' . $lotteries['__by_id'][$chartdescex[0]]['name'] . ' - Maximum insurance tier: ' . $chartdescex[1] . '</h1>';
            echo '<canvas id="myChart_' . $chartdesc . '"></canvas>';
            echo '<script type="text/javascript">';
            echo "
			var ctx = document.getElementById('myChart_" . $chartdesc . "').getContext('2d');
			var chart = new Chart(ctx, {
				// The type of chart we want to create
				type: 'line',

				// The data for our dataset
				data: {
					labels: [100, 1000, 5000, 10000, 50000, 100000, 500000, 1000000, 10000000, 100000000, 1000000000],
					datasets: [";
            foreach ($chartitems as $dataset_key => $dataset_value) {
                echo "{
						label: 'Total income for jackpot " . Lotto_View::format_currency($dataset_key * 1000000, $currencies[$lotteries['__by_id'][$chartdescex[0]]['currency_id']]['code']) . "',
						fill: false,
						borderColor: 'rgb(" . rand(0, 255) . ", " . rand(0, 255) . ", " . rand(0, 255) . ")',
						data: [" . implode(", ", $dataset_value) . "],
					},";
            }
            echo "]
				},

				// Configuration options go here
				options: {
				}
			});
";
            echo '</script>';
        }

        echo '</body></html>';

//        echo '<h1>Warnings</h1>';
//        foreach ($warnings AS $warning) {
//            echo 'Lottery: ' . $warning[0]['name'] . '<br>';
//            echo 'Jackpot: ' . Lotto_View::format_currency($warning[1] * 1000000, $currencies[$warning[0]['currency_id']]['code']) . '<br>';
//            echo 'Maximum insurance tier: ' . $warning[2] . '<br>';
//            echo 'Purchases count: ' . $warning[3] . '<br>';
//            echo 'Total income: ' . Lotto_View::format_currency($warning[4], $currencies[$warning[0]['currency_id']]['code']) . '<br><br>';
//        }
    }

    public function action_updatephones()
    {
        $users = Model_Whitelabel_User::find_all();
        $countries = Lotto_Helper::get_localized_country_list();
        foreach ($users as $user) {
            if (!empty($user['phone'])) {
                $phone = $user['phone'];
                if (isset($countries[$user['country']])) {
                    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
                    try {
                        $pNumber = $phoneUtil->parse($phone, $user['country']);
                        $isValid = $phoneUtil->isValidNumber($pNumber);
                        if ($isValid) {
                            $user->set([
                                "phone_country" => $user['country'],
                                "phone" => $phoneUtil->format($pNumber, \libphonenumber\PhoneNumberFormat::E164)
                            ]);
                            $user->save();
                        } else {
                            echo 'bad phone';
                        }
                    } catch (\libphonenumber\NumberParseException $e) {
                        echo 'bad phone2';
                    }
                } else {
                    echo 'bad country';
                }
            }
        }
    }

    public function action_historylottopl()
    {
        set_time_limit(0);

        $doc = new DOMDocument();
        @$doc->loadHTML(Lotto_Helper::load_HTML_url("http://megalotto.pl/wyniki/lotto/losowania-od-10-Pa%C5%BAdziernika-2009-do-6-Stycznia-2018", 360));
        $xpath = new DOMXPath($doc);

        $query = "//div[contains(concat(@class, ' '), 'lista_ostatnich_losowan ')]/ul";
        $sdraws = $xpath->query($query);

        $draws = [];
        foreach ($sdraws as $draw) {
            $date = DateTime::createFromFormat("d-m-Y", trim($draw->getElementsByTagName("li")->item(1)->nodeValue));
            $numbers = $xpath->query("li[contains(concat(@class, ' '), 'numbers_in_list ')]", $draw);
            $link = $draw->getElementsByTagName("a")->item(0)->getAttribute("href");

            $arr_numbers = [];
            foreach ($numbers as $item) {
                $arr_numbers[] = trim($item->nodeValue);
            }
            $draws[$date->format("Y-m-d")] = [[], $arr_numbers];
        }

        $res = DB::query("SELECT date_local AS `date` FROM lottery_draw WHERE lottery_id = 7 ORDER BY date_local DESC");
        $res = $res->execute()->as_array();

        foreach ($res as $dbdraw) {
            unset($draws[$dbdraw['date']]);
        }

        if (!count($draws)) {
            exit("no more draws");
        }

        $draws = array_reverse($draws, true);


        $i = 0;
        foreach ($draws as $draw_date => $numbers) {
            $i++;
            if ($i == 6) {
                // download 5 at once
                break;
            }
            $xdate = DateTime::createFromFormat(Helpers_Time::DATETIME_FORMAT, $draw_date);

            $doc = new DOMDocument();
            @$doc->loadHTML(Lotto_Helper::load_HTML_url("http://megalotto.pl/wyniki/lotto/wygrane-z-dnia-" . $xdate->format("d-m-Y"), 360));

            $xpath = new DOMXPath($doc);

            $query = "//table[contains(concat(@class, ' '), 'dl_wygrane_table ')]/tr";
            $trs = $xpath->query($query);

            $prizes = [];

            foreach ($trs as $key => $tr) {
                if ($key == 0) {
                    continue;
                }

                $tds = $tr->getElementsByTagName("td");
                $winner_cnt = intval(trim($tds->item(1)->nodeValue));
                $amount = trim(str_replace(" ", "", $tds->item(2)->nodeValue));
                $prizes[] = [$winner_cnt, $amount];
            }
            $draws[$draw_date][0] = $prizes;

            $draw = Model_Lottery_Draw::forge();

            $dt = new DateTime("now", new DateTimeZone("UTC"));
            $draw->set([
                'lottery_id' => 7,
                'date_download' => $dt->format(Helpers_Time::DATETIME_FORMAT),
                'date_local' => $draw_date,
                'jackpot' => 0,
                'numbers' => implode(',', $draws[$draw_date][1]),
                'bnumbers' => null,
                'lottery_type_id' => 7/* $lottery['lottery_type_id'] */,
                'total_prize' => 0,
                'total_winners' => 0,
                'final_jackpot' => 0
            ]);

            $draw->save();

            // now prizes
            if (!count($prizes)) {
                throw new Exception('Helper - No prizes!');
            }

            $type_data = Model_Lottery_Type_Data::find(
                [
                    'where' => [
                        'lottery_type_id' => 7/* ***** */,
                    ],
                    'order_by' => 'id'
                ]
            );
            if (count($type_data) != count($prizes)) {
                throw new Exception('Helper - Type-prize mismatch');
            }

//            if (!isset($prizes[0][1])) {
//                if ($prizes[0][0] != 0) {
//                    $prizes[0][1] = $draw->jackpot * 1000000;
//                } else {
//                    $prizes[0][1] = 0;
//                }
//            }

            if ($prizes[0][0] == 0) {
                $prizes[0][1] = 0;
            }
            $total_winners = 0;
            $total_prize = 0;
            foreach ($prizes as $prize) {
                $total_winners += $prize[0];
                $total_prize += $prize[1] * $prize[0];
            }

            $draw->set([
                'final_jackpot' => $prizes[0][1],
                'total_winners' => $total_winners,
                'total_prize' => $total_prize
            ]);
            $draw->save();

            foreach ($prizes as $key => $value) {
                $prize_data = Model_Lottery_Prize_Data::forge();
                $prize_data->set(
                    [
                        'lottery_draw_id' => $draw->id,
                        'lottery_type_data_id' => $type_data[$key]['id'],
                        'winners' => $value[0],
                        'prizes' => $value[1]
                    ]
                );
                $prize_data->save();
            }
        }
    }

    public function action_testLott()
    {
        $ch = curl_init();
        $json = json_encode(["CompanyId" => 'NTLotteries',
            "MaxDrawCountPerProduct" => 1,
            "OptionalProductFilter" => [0 => "OzLotto"]]);
        curl_setopt($ch, CURLOPT_URL, "https://api.thelott.com/sales/vmax/web/data/lotto/opendraws");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);
        if ($data == true) {
            $data = json_decode($data);
            $draw = $data->Draws[0];
            $jackpot = round(str_replace(["float(", ")"], "", $draw->Div1Amount) / 1000000, 2);
        } else {
            $jackpot = false;
        }
        curl_close($ch);

        $ch = curl_init();
        $json = json_encode(["CompanyId" => 'NTLotteries',
            "MaxDrawCountPerProduct" => 1,
            "OptionalProductFilter" => [0 => "OzLotto"]]);
        curl_setopt($ch, CURLOPT_URL, "https://api.thelott.com/sales/vmax/web/data/lotto/latestresults");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);
        if ($data == true) {
            $data = json_decode($data);
            $draw = $data->DrawResults[0];

            $date = date('d/m/Y', strtotime($draw->DrawDate));
            $date = DateTime::createFromFormat(
                'd/m/Y H:i',
                $date . ' 19:30',
                new \DateTimeZone("Australia/Melbourne")
            );
            $date_utc = clone $date;
            $date_utc->setTimezone(new \DateTimeZone("UTC"));
            $numbers = $draw->PrimaryNumbers;
            if (count($numbers) != 7) {
                throw new Exception('Bad number length.');
            }
            $bonus_numbers = $draw->SecondaryNumbers;
            if (count($bonus_numbers) != 2) {
                throw new Exception('Bad bonus number value length.');
            }
            foreach ($draw->Dividends as $division) {
                $winners_prz = round(
                    str_replace(["float(", ")"], "", $division->BlocDividend),
                    2
                );
                $prizes[] = [$division->BlocNumberOfWinners, $winners_prz];
            }
            if (count($prizes) != 7) {
                throw new Exception('Bad prizes length. Probably Dividends property does not exist in Ltech response.');
            }
        }
        curl_close($ch);
    }

    public function action_historyozlotto()
    {
        set_time_limit(0);
        $dates = [ // max data range of one request is 90 day - we need to slice the dates
            0 => [
                'start' => "01-01",
                'end' => "03-15"
            ],
            2 => [
                'start' => "03-16",
                'end' => "06-01"
            ],
            3 => [
                'start' => "06-02",
                'end' => "08-15"
            ],
            4 => [
                'start' => "08-16",
                'end' => "11-01"
            ],
            5 => [
                'start' => "11-01",
                'end' => "12-31"
            ]
        ];
        for ($i = 2012; $i < 2019; $i++) {
            foreach ($dates as $date) {
                $ch = curl_init();
                $json = json_encode([
                    "CompanyFilter" => [0 => 'NTLotteries'],
                    "DateEnd" => $i . "-" . $date['end'] . "T21:59:59Z",
//                    "DateEnd" => "2012-08-15T21:59:59Z",
                    "DateStart" => $i . "-" . $date['start'] . "T22:00:00Z",
//                    "DateStart" => "2012-08-06T22:00:00Z",
                    "ProductFilter" => [0 => "OzLotto"]
                ]);
                curl_setopt($ch, CURLOPT_URL, "https://api.thelott.com/sales/vmax/web/data/lotto/results/search/daterange");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $data = curl_exec($ch);
                if ($data == true) {
                    $data = json_decode($data);
                    foreach (array_reverse($data->Draws) as $d) {
                        $prizes = [];
                        $date = date('d/m/Y', strtotime($d->DrawDate));
                        $date = DateTime::createFromFormat(
                            'd/m/Y H:i',
                            $date . ' 19:30',
                            new \DateTimeZone("Australia/Melbourne")
                        );
                        $date_utc = clone $date;
                        $date_utc->setTimezone(new \DateTimeZone("UTC"));
                        $numbers = $d->PrimaryNumbers;
                        $bonus_numbers = $d->SecondaryNumbers;
                        foreach ($d->Dividends as $division) {
                            $winners_prz = round(
                                str_replace(["float(", ")"], "", $division->BlocDividend),
                                2
                            );
                            $prizes[] = [$division->BlocNumberOfWinners, $winners_prz];
                        }
                        if (!empty($prizes)) {
                            $draw = Model_Lottery_Draw::forge();
                            $dt = new DateTime("now", new DateTimeZone("UTC"));
                            $draw->set([
                                'lottery_id' => 10,
                                'date_download' => $dt->format(Helpers_Time::DATETIME_FORMAT),
                                'date_local' => $date->format(Helpers_Time::DATETIME_FORMAT),
                                'jackpot' => 0,
                                'numbers' => implode(',', $numbers),
                                'bnumbers' => implode(',', $bonus_numbers),
                                'lottery_type_id' => 12/* $lottery['lottery_type_id'] */,
                                'total_prize' => 0,
                                'total_winners' => 0,
                                'final_jackpot' => 0
                            ]);

                            $draw->save();
                            $type_data = Model_Lottery_Type_Data::find(
                                [
                                    'where' => [
                                        'lottery_type_id' => 12 /* ***** */,
                                    ],
                                    'order_by' => 'id'
                                ]
                            );

                            if ($prizes[0][0] == 0) {
                                $prizes[0][1] = 0;
                            }
                            $total_winners = 0;
                            $total_prize = 0;
                            $n = 0;


                            foreach ($prizes as $prize) {
                                $total_winners += $prize[0];
                                $total_prize += $prize[1] * $prize[0];
                            }
                            $draw->set([
                                'final_jackpot' => $prizes[0][1],
                                'total_winners' => $total_winners,
                                'total_prize' => $total_prize
                            ]);
                            $draw->save();
                            foreach ($prizes as $key => $value) {
                                $prize_data = Model_Lottery_Prize_Data::forge();
                                $prize_data->set(
                                    [
                                        'lottery_draw_id' => $draw->id,
                                        'lottery_type_data_id' => $type_data[$key]['id'],
                                        'winners' => $value[0],
                                        'prizes' => $value[1]
                                    ]
                                );
                                $prize_data->save();
                            }
                            echo "Success: " . $date->format('Y-m-d H:i:s') . "<br>";
                        } else {
                            echo "No prizes at:" . $date->format('Y-m-d H:i:s') . "<br>";
                        }
                    }
                }
                curl_close($ch);
                sleep(1);
            }
        }
    }

    public function action_historypowerballau()
    {
        set_time_limit(0);
        $ch = curl_init();
        $json = json_encode([
            "CompanyFilter" => [0 => 'NTLotteries'],
            "DateEnd" => "2018-06-30T21:59:59Z",
            "DateStart" => "2018-04-15T22:00:00Z",
            "ProductFilter" => [0 => "powerball"]
        ]);
        curl_setopt($ch, CURLOPT_URL, "https://api.thelott.com/sales/vmax/web/data/lotto/results/search/daterange");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);
        if ($data == true) {
            $data = json_decode($data);
            foreach (array_reverse($data->Draws) as $d) {
                $prizes = [];
                $date = date('d/m/Y', strtotime($d->DrawDate));
                $date = DateTime::createFromFormat(
                    'd/m/Y H:i',
                    $date . ' 19:30',
                    new \DateTimeZone("Australia/Melbourne")
                );
                $date_utc = clone $date;
                $date_utc->setTimezone(new \DateTimeZone("UTC"));
                $numbers = $d->PrimaryNumbers;
                $bonus_numbers = $d->SecondaryNumbers;
                foreach ($d->Dividends as $division) {
                    $winners_prz = round(
                        str_replace(["float(", ")"], "", $division->BlocDividend),
                        2
                    );
                    $prizes[] = [$division->BlocNumberOfWinners, $winners_prz];
                }
                if (!empty($prizes)) {
                    $draw = Model_Lottery_Draw::forge();
                    $dt = new DateTime("now", new DateTimeZone("UTC"));
                    $draw->set([
                        'lottery_id' => 11,
                        'date_download' => $dt->format(Helpers_Time::DATETIME_FORMAT),
                        'date_local' => $date->format(Helpers_Time::DATETIME_FORMAT),
                        'jackpot' => 0,
                        'numbers' => implode(',', $numbers),
                        'bnumbers' => implode(',', $bonus_numbers),
                        'lottery_type_id' => 13/* $lottery['lottery_type_id'] */,
                        'total_prize' => 0,
                        'total_winners' => 0,
                        'final_jackpot' => 0
                    ]);

                    $draw->save();
                    $type_data = Model_Lottery_Type_Data::find(
                        [
                            'where' => [
                                'lottery_type_id' => 13/* ***** */,
                            ],
                            'order_by' => 'id'
                        ]
                    );

                    if ($prizes[0][0] == 0) {
                        $prizes[0][1] = 0;
                    }
                    $total_winners = 0;
                    $total_prize = 0;
                    $n = 0;


                    foreach ($prizes as $prize) {
                        $total_winners += $prize[0];
                        $total_prize += $prize[1] * $prize[0];
                    }
                    $draw->set([
                        'final_jackpot' => $prizes[0][1],
                        'total_winners' => $total_winners,
                        'total_prize' => $total_prize
                    ]);
                    $draw->save();
                    foreach ($prizes as $key => $value) {
                        $prize_data = Model_Lottery_Prize_Data::forge();
                        $prize_data->set(
                            [
                                'lottery_draw_id' => $draw->id,
                                'lottery_type_data_id' => $type_data[$key]['id'],
                                'winners' => $value[0],
                                'prizes' => $value[1]
                            ]
                        );
                        $prize_data->save();
                    }
                    echo "Success: " . $date->format('Y-m-d H:i:s') . "<br>";
                } else {
                    echo "No prizes at:" . $date->format('Y-m-d H:i:s') . "<br>";
                }
            }
        }
        curl_close($ch);
        sleep(1);
    }

    public function action_historysaturdaylotto()
    {
        set_time_limit(0);
        $dates = [ // max data range of one request is 90 day - we need to slice the dates
            0 => [
                'start' => "01-01",
                'end' => "03-15"
            ],
            2 => [
                'start' => "03-16",
                'end' => "06-01"
            ],
            3 => [
                'start' => "06-02",
                'end' => "08-15"
            ],
            4 => [
                'start' => "08-16",
                'end' => "11-01"
            ],
            5 => [
                'start' => "11-01",
                'end' => "12-31"
            ]
        ];
        for ($i = 2012; $i < 2019; $i++) {
            foreach ($dates as $date) {
                $ch = curl_init();
                $json = json_encode([
                    "CompanyFilter" => [0 => 'NTLotteries'],
                    "DateEnd" => $i . "-" . $date['end'] . "T21:59:59Z",
//                    "DateEnd" => "2012-08-15T21:59:59Z",
                    "DateStart" => $i . "-" . $date['start'] . "T22:00:00Z",
//                    "DateStart" => "2012-08-06T22:00:00Z",
                    "ProductFilter" => [0 => "TattsLotto"]
                ]);
                curl_setopt($ch, CURLOPT_URL, "https://api.thelott.com/sales/vmax/web/data/lotto/results/search/daterange");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $data = curl_exec($ch);
                if ($data == true) {
                    $data = json_decode($data);
                    foreach (array_reverse($data->Draws) as $d) {
                        $prizes = [];
                        $date = date('d/m/Y', strtotime($d->DrawDate));
                        $date = DateTime::createFromFormat(
                            'd/m/Y H:i',
                            $date . ' 19:30',
                            new \DateTimeZone("Australia/Melbourne")
                        );
                        $date_utc = clone $date;
                        $date_utc->setTimezone(new \DateTimeZone("UTC"));
                        $numbers = $d->PrimaryNumbers;
                        $bonus_numbers = $d->SecondaryNumbers;
                        foreach ($d->Dividends as $division) {
                            $winners_prz = round(
                                str_replace(["float(", ")"], "", $division->BlocDividend),
                                2
                            );
                            $prizes[] = [$division->BlocNumberOfWinners, $winners_prz];
                        }
                        if (!empty($prizes)) {
                            $draw = Model_Lottery_Draw::forge();
                            $dt = new DateTime("now", new DateTimeZone("UTC"));
                            $draw->set([
                                'lottery_id' => 12,
                                'date_download' => $dt->format(Helpers_Time::DATETIME_FORMAT),
                                'date_local' => $date->format(Helpers_Time::DATETIME_FORMAT),
                                'jackpot' => 0,
                                'numbers' => implode(',', $numbers),
                                'bnumbers' => implode(',', $bonus_numbers),
                                'lottery_type_id' => 14/* $lottery['lottery_type_id'] */,
                                'total_prize' => 0,
                                'total_winners' => 0,
                                'final_jackpot' => 0
                            ]);

                            $draw->save();
                            $type_data = Model_Lottery_Type_Data::find(
                                [
                                    'where' => [
                                        'lottery_type_id' => 14 /* ***** */,
                                    ],
                                    'order_by' => 'id'
                                ]
                            );

                            if ($prizes[0][0] == 0) {
                                $prizes[0][1] = 0;
                            }
                            $total_winners = 0;
                            $total_prize = 0;
                            $n = 0;


                            foreach ($prizes as $prize) {
                                $total_winners += $prize[0];
                                $total_prize += $prize[1] * $prize[0];
                            }
                            $draw->set([
                                'final_jackpot' => $prizes[0][1],
                                'total_winners' => $total_winners,
                                'total_prize' => $total_prize
                            ]);
                            $draw->save();
                            foreach ($prizes as $key => $value) {
                                $prize_data = Model_Lottery_Prize_Data::forge();
                                $prize_data->set(
                                    [
                                        'lottery_draw_id' => $draw->id,
                                        'lottery_type_data_id' => $type_data[$key]['id'],
                                        'winners' => $value[0],
                                        'prizes' => $value[1]
                                    ]
                                );
                                $prize_data->save();
                            }
                            echo "Success: " . $date->format('Y-m-d H:i:s') . "<br>";
                        } else {
                            echo "No prizes at:" . $date->format('Y-m-d H:i:s') . "<br>";
                        }
                    }
                }
                curl_close($ch);
                sleep(4);
            }
        }
    }

    public function action_historymondaywednesdaylotto()
    {
        set_time_limit(0);
        $dates = [ // max data range of one request is 90 day - we need to slice the dates
            0 => [
                'start' => "01-01",
                'end' => "03-15"
            ],
            2 => [
                'start' => "03-16",
                'end' => "06-01"
            ],
            3 => [
                'start' => "06-02",
                'end' => "08-15"
            ],
            4 => [
                'start' => "08-16",
                'end' => "11-01"
            ],
            5 => [
                'start' => "11-01",
                'end' => "12-31"
            ]
        ];
        for ($i = 2012; $i < 2019; $i++) {
            foreach ($dates as $date) {
                $ch = curl_init();
                $json = json_encode([
                    "CompanyFilter" => [0 => 'NTLotteries'],
                    "DateEnd" => $i . "-" . $date['end'] . "T21:59:59Z",
//                    "DateEnd" => "2018-04-20T21:59:59Z",
                    "DateStart" => $i . "-" . $date['start'] . "T22:00:00Z",
//                    "DateStart" => "2018-02-20T22:00:00Z",
                    "ProductFilter" => [0 => "MonWedLotto"]
                ]);
                curl_setopt($ch, CURLOPT_URL, "https://api.thelott.com/sales/vmax/web/data/lotto/results/search/daterange");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $data = curl_exec($ch);
                if ($data == true) {
                    $data = json_decode($data);
                    foreach (array_reverse($data->Draws) as $d) {
                        $prizes = [];
                        $date = date('d/m/Y', strtotime($d->DrawDate));
                        $date = DateTime::createFromFormat(
                            'd/m/Y H:i',
                            $date . ' 19:30',
                            new \DateTimeZone("Australia/Melbourne")
                        );
                        $date_utc = clone $date;
                        $date_utc->setTimezone(new \DateTimeZone("UTC"));
                        $numbers = $d->PrimaryNumbers;
                        $bonus_numbers = $d->SecondaryNumbers;
                        foreach ($d->Dividends as $division) {
                            $winners_prz = round(
                                str_replace(["float(", ")"], "", $division->BlocDividend),
                                2
                            );
                            $prizes[] = [$division->BlocNumberOfWinners, $winners_prz];
                        }
                        if (!empty($prizes)) {
                            $draw = Model_Lottery_Draw::forge();
                            $dt = new DateTime("now", new DateTimeZone("UTC"));
                            $draw->set([
                                'lottery_id' => 13,
                                'date_download' => $dt->format(Helpers_Time::DATETIME_FORMAT),
                                'date_local' => $date->format(Helpers_Time::DATETIME_FORMAT),
                                'jackpot' => 0,
                                'numbers' => implode(',', $numbers),
                                'bnumbers' => implode(',', $bonus_numbers),
                                'lottery_type_id' => 15/* $lottery['lottery_type_id'] */,
                                'total_prize' => 0,
                                'total_winners' => 0,
                                'final_jackpot' => 0
                            ]);

                            $draw->save();
                            $type_data = Model_Lottery_Type_Data::find(
                                [
                                    'where' => [
                                        'lottery_type_id' => 15/* ***** */,
                                    ],
                                    'order_by' => 'id'
                                ]
                            );

                            if ($prizes[0][0] == 0) {
                                $prizes[0][1] = 0;
                            }
                            $total_winners = 0;
                            $total_prize = 0;
                            $n = 0;


                            foreach ($prizes as $prize) {
                                $total_winners += $prize[0];
                                $total_prize += $prize[1] * $prize[0];
                            }
                            $draw->set([
                                'final_jackpot' => $prizes[0][1],
                                'total_winners' => $total_winners,
                                'total_prize' => $total_prize
                            ]);
                            $draw->save();
                            foreach ($prizes as $key => $value) {
                                $prize_data = Model_Lottery_Prize_Data::forge();
                                $prize_data->set(
                                    [
                                        'lottery_draw_id' => $draw->id,
                                        'lottery_type_data_id' => $type_data[$key]['id'],
                                        'winners' => $value[0],
                                        'prizes' => $value[1]
                                    ]
                                );
                                $prize_data->save();
                            }
                            echo "Success: " . $date->format('Y-m-d H:i:s') . "<br>";
                        } else {
                            echo "No prizes at:" . $date->format('Y-m-d H:i:s') . "<br>";
                        }
                    }
                }
                curl_close($ch);
                sleep(1);
            }
        }
    }

    public function action_historyelgordo()
    {
        set_time_limit(0);
        $this->get_data_from_url_elgordo('https://www.loteriasyapuestas.es/en/gordo-primitiva/sorteos/2010/712705001');
    }

    public function get_data_from_url_elgordo($url)
    {
        set_time_limit(0);
        libxml_use_internal_errors(true);

        $bonus_numbers = [];
        $additional_data = [];

        $doc2 = new DOMDocument();
        $doc2->loadHTML(Lotto_Helper::load_HTML_url($url));
        $xpath2 = new DOMXPath($doc2);

        // Check if next link exist
        $query = "//div[contains(@class, 'resultadoSiguiente')]/a";
        $next = $xpath2->query($query);
        if ($next->length == 1) {
            $next = trim($next->item(0)->getAttribute('href'));
        } else {
            echo "<br>END";
            die();
        }

        $query = "(//div[contains(@class, 'cuerpoRegionIzq')]/ul)[1]/li";
        $draw = $xpath2->query($query);
        foreach ($draw as $key => $item) {
            $value = intval($item->nodeValue);
            if ($value <= 0) {
                throw new Exception('Bad number value (<=0).');
            }
            $numbers[] = $value;
        }

        // Refund/jackot
        $query = "//span[contains(@class, 'bolaPeq')]";

        $draw = $xpath2->query($query);

        if ($draw->length != 1) {
            throw new Exception('Bad bonus number length.');
        }
        foreach ($draw as $key => $item) {
            $value = intval($item->nodeValue);
            if ($value > 9) {
                throw new Exception('Bad bonus number value (>9).');
            }
            //$bonus_numbers['refund'] = $value;
            $additional_data['refund'] = $value;
        }


        $query = "(//div[contains(@class, 'tituloRegion')])[2]";
        $date = $xpath2->query($query);
        if ($date->length != 1) {
            throw new Exception('Bad date.');
        }
        $date = trim($date->item(0)->nodeValue);
        $date = explode(',', $date);
        $date = date('d/m/Y', strtotime($date[2]));
        $date = DateTime::createFromFormat('d/m/Y H:i', $date . ' 21:30', new \DateTimeZone("Europe/Madrid"));
        $date_utc = clone $date;
        $date_utc->setTimezone(new \DateTimeZone("UTC"));


        $query = "//table[contains(@class, 'tablaDetalle')]";
        $prizedata = $xpath2->query($query);

        if ($prizedata->length < 1) {
            throw new Exception('Bad prize data length.');
        }

        $prizedata = $prizedata->item(0)->getElementsByTagName('tr');

        if ($prizedata->length != 10) {
            throw new Exception('Bad prize data tr length.');
        }

        for ($i = 1; $i < 10; $i++) {
            $tds = $prizedata->item($i)->getElementsByTagName('td');
            if ($tds->length < 3) {
                throw new Exception('Bad prize data td length.');
            }
            $winners_cnt = trim($tds->item(1)->nodeValue);
//            $winners_cnt = str_replace('.', '', $winners_cnt);
            $winners_cnt = str_replace(',', '', $winners_cnt);
            $winners_prz = trim($tds->item(2)->nodeValue);
            if (preg_match('/^[0-9.,]+/u', $winners_prz, $m)) {
                $winners_prz = $m[0];
//                $winners_prz = str_replace('.', '', $winners_prz);
                $winners_prz = str_replace(',', '', $winners_prz);
            } else {
//                throw new Exception('Bad prize data preg.');
                echo "No prize data in: " . $date->format('Y-m-d H:i:s') . "<br>";
                $this->get_data_from_url_elgordo('https://www.loteriasyapuestas.es' . $next);
            }

            $prizes[] = [$winners_cnt, $winners_prz];
        }

        $draw = Model_Lottery_Draw::forge();
        $dt = new DateTime("now", new DateTimeZone("UTC"));
        $draw->set([
            'lottery_id' => 14,
            'date_download' => $dt->format(Helpers_Time::DATETIME_FORMAT),
            'date_local' => $date->format(Helpers_Time::DATETIME_FORMAT),
            'jackpot' => 0,
            'numbers' => implode(',', $numbers),
            'bnumbers' => implode(',', $bonus_numbers),
            'lottery_type_id' => 16/* $lottery['lottery_type_id'] */,
            'total_prize' => 0,
            'total_winners' => 0,
            'final_jackpot' => 0,
            'additional_data' => serialize($additional_data)
        ]);

        $draw->save();

        if (!count($prizes)) {
            throw new Exception('Helper - No prizes!');
        }

        $type_data = Model_Lottery_Type_Data::find(
            [
                'where' => [
                    'lottery_type_id' => 16 /* ***** */,
                ],
                'order_by' => 'id'
            ]
        );
        if (count($type_data) != count($prizes)) {
            throw new Exception('Helper - Type-prize mismatch');
        }

        if ($prizes[0][0] == 0) {
            $prizes[0][1] = 0;
        }
        $total_winners = 0;
        $total_prize = 0;
        $n = 0;


        foreach ($prizes as $prize) {
            $total_winners += $prize[0];
            $total_prize += $prize[1] * $prize[0];
        }
        $draw->set([
            'final_jackpot' => $prizes[0][1],
            'total_winners' => $total_winners,
            'total_prize' => $total_prize
        ]);
        $draw->save();

        foreach ($prizes as $key => $value) {
            $prize_data = Model_Lottery_Prize_Data::forge();
            $prize_data->set(
                [
                    'lottery_draw_id' => $draw->id,
                    'lottery_type_data_id' => $type_data[$key]['id'],
                    'winners' => $value[0],
                    'prizes' => $value[1]
                ]
            );
            $prize_data->save();
        }
        echo "Success: " . $date->format('Y-m-d H:i:s') . "<br>";
        $this->get_data_from_url_elgordo('https://www.loteriasyapuestas.es' . $next);
    }

    public function action_historyeurojackpot()
    {
        set_time_limit(0);

        $doc = Lotto_Helper::load_HTML_url("https://www.eurojackpot.de/data");
        $doc = explode("\n", $doc);
        $key = 0;
        $found = false;
        foreach ($doc as $key => $item) {
            if (mb_strpos($item, "window.mrm.data.gewinnzahlen=") !== false) {
                $found = true;
                break;
            }
        }
        if ($found) {
            $json_data = mb_substr($doc[$key], strlen("window.mrm.data.gewinnzahlen="), -1);
            $json_data = json_decode($json_data, true);
            foreach ($json_data as $key => $item) {
                $itemdate = DateTime::createFromFormat('d.m.Y H:i', $key . ' 21:00');
                $newrules = new DateTime("2014-10-10 00:00:00");
                if ($itemdate >= $newrules) {
                    $draws[$itemdate->format("Y-m-d")] = [[], $item[0]['numbers'], $item[1]['numbers']];
                }
            }

            $res = DB::query("SELECT date_local AS `date` FROM lottery_draw WHERE lottery_id = 3 ORDER BY date_local DESC");
            $res = $res->execute()->as_array();

            foreach ($res as $dbdraw) {
                unset($draws[$dbdraw['date']]);
            }

            if (!count($draws)) {
                exit("no more draws");
            }
            $draws = array_reverse($draws, true);

            $i = 0;
            foreach ($draws as $draw_date => $numbers) {
                $i++;
                if ($i == 6) {
                    // download 5 at once
                    break;
                }
                $xdate = DateTime::createFromFormat(Helpers_Time::DATETIME_FORMAT, $draw_date);
                $doc = Lotto_Helper::load_HTML_url("https://eurojackpot.de/quota?date=" . $xdate->format("d.m.Y"));
                $json_data = json_decode($doc, true);
                $winners = $json_data['quota'];
                $prizes = [];

                foreach ($winners as $winner) {
                    $amount = $winner['amount'];
                    if ($amount == "unbesetzt") {
                        $amount = "0";
                    }
                    if (preg_match('/^([0-9.,]+)/u', $amount, $m)) {
                        $amount = $m[1];
                        $amount = str_replace('.', '', $amount);
                        $amount = str_replace(',', '.', $amount);
                        $winner_cnt = str_replace('.', '', $winner['winners']);
                        $prizes[] = [$winner_cnt, $amount];
                    } else {
                        throw new Exception('Winners parse error.');
                    }
                }
                $draws[$draw_date][0] = $prizes;

                $draw = Model_Lottery_Draw::forge();

                $dt = new DateTime("now", new DateTimeZone("UTC"));
                $draw->set([
                    'lottery_id' => 3,
                    'date_download' => $dt->format(Helpers_Time::DATETIME_FORMAT),
                    'date_local' => $draw_date,
                    'jackpot' => 0,
                    'numbers' => implode(',', $draws[$draw_date][1]),
                    'bnumbers' => implode(',', $draws[$draw_date][2]),
                    'lottery_type_id' => 3/* $lottery['lottery_type_id'] */,
                    'total_prize' => 0,
                    'total_winners' => 0,
                    'final_jackpot' => 0
                ]);

                $draw->save();

                // now prizes
                if (!count($prizes)) {
                    throw new Exception('Helper - No prizes!');
                }

                $type_data = Model_Lottery_Type_Data::find(
                    [
                        'where' => [
                            'lottery_type_id' => 3/* ***** */,
                        ],
                        'order_by' => 'id'
                    ]
                );
                if (count($type_data) != count($prizes)) {
                    throw new Exception('Helper - Type-prize mismatch');
                }

//                if (!isset($prizes[0][1])) {
//                    if ($prizes[0][0] != 0) {
//                        $prizes[0][1] = $draw->jackpot * 1000000;
//                    } else {
//                        $prizes[0][1] = 0;
//                    }
//                }

                if ($prizes[0][0] == 0) {
                    $prizes[0][1] = 0;
                }
                $total_winners = 0;
                $total_prize = 0;
                foreach ($prizes as $prize) {
                    $total_winners += $prize[0];
                    $total_prize += $prize[1] * $prize[0];
                }

                $draw->set([
                    'final_jackpot' => $prizes[0][1],
                    'total_winners' => $total_winners,
                    'total_prize' => $total_prize
                ]);
                $draw->save();

                foreach ($prizes as $key => $value) {
                    $prize_data = Model_Lottery_Prize_Data::forge();
                    $prize_data->set(
                        [
                            'lottery_draw_id' => $draw->id,
                            'lottery_type_data_id' => $type_data[$key]['id'],
                            'winners' => $value[0],
                            'prizes' => $value[1]
                        ]
                    );
                    $prize_data->save();
                }
            }
        }
    }

    /**
     * Find all wrong timezones connected with locales
     *
     */
    public function action_checkwronglocales()
    {
        $wrong_timezones = [];
        $all_locales = [];
        $all_locales_db = Model_Language::get_all_languages();

        foreach ($all_locales_db as $key => $single_locale) {
            $all_locales[] = $single_locale['code'];
        }

        $timezones = DateTimeZone::listIdentifiers();

        foreach ($timezones as $timezone) {
            $dtzone = new DateTimeZone($timezone);

            foreach ($all_locales as $key => $locale_default) {
                try {
                    $fmt = new IntlDateFormatter($locale_default, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $dtzone, IntlDateFormatter::GREGORIAN, "zzz");
                } catch (Exception $e) {
//                    $wrong_timezones[$timezone][] = (string) $locale_default;
                    $wrong_timezones[$locale_default][] = (string)$timezone;
                }
            }
        }

        echo "<br><br>Wrong Timezones:<br>";
        echo json_encode($wrong_timezones);
        echo "<br><br>";
    }

    /**
     * Clear all lotteries dates to make possible
     * to pull latest data from servers
     */
    public function action_clearlotteries()
    {
        $lotteries = Model_Lottery::find_all();

        echo "<br><br>VALUE:<br>";
        var_dump($lotteries);
        echo "<br><br>";

        foreach ($lotteries as $lottery) {
            $lottery->set([
                'next_date_local' => null,
                'next_date_utc' => null,
                'last_date_local' => null
            ]);
            $lottery->save();
        }

        Lotto_Helper::clear_cache(['model_lottery']);

        $lotteries = Model_Lottery::get_all_lotteries();

        echo "<br><br>Lotteries after clear:<br>";
        var_dump($lotteries['__by_id']);
        echo "<br><br>";
    }

    public function action_checkdefdep()
    {
        $record_from_currencies = Helpers_Currency::get_mtab_currency(true, "VEF");

        var_dump($record_from_currencies);

        $record_eur = Helpers_Currency::get_mtab_currency(true, "EUR");

        var_dump($record_eur);

        $edit_data = [
            'default_deposit_first_box' => 10.00,
            'default_deposit_second_box' => 20.00,
            'default_deposit_third_box' => 50.00,
        ];

        $results = Helpers_Currency::get_default_deposits_from_currency(
            $record_from_currencies,
            $edit_data
        );

        var_dump($results);
    }

    public function action_checkcc()
    {
        $whitelabel = Model_Whitelabel::find_by_id(1);
        $whitelabel = $whitelabel[0];
        echo "<br><br>Whitelabel:<br>";
        var_dump($whitelabel);
        echo "<br><br>";
//        die();
        $user = Model_Whitelabel_User::find_by_id(8);
        $user = $user[0];
        echo "<br><br>USER:<br>";
        var_dump($user);
        echo "<br><br>";
//        die();
        $ccmethods = Model_Whitelabel_CC_Method::get_cc_methods_for_whitelabel($whitelabel);
//        $ccmethods = $ccmethods[$whitelabel['id']];
        echo "<br><br>VALUE:<br>";
        echo json_encode($ccmethods);
        echo "<br><br>";
//        die();

        $ccmethods_merchant = [];
        foreach ($ccmethods as $ccmethod) {
            echo "Method: <br>";
            echo json_encode($ccmethod);
            $ccmethods_merchant[intval($ccmethod['method'])] = $ccmethod;
        }
        // check if emerchant is set
        if (empty($ccmethods_merchant[1])) {
            exit("HMM");
        }
        $pdata = unserialize($ccmethods_merchant[1]['settings']);

        echo "<br><br>PDATA:<br>";
        echo json_encode($pdata);
        echo "<br><br>";

        require_once APPPATH . "vendor/emerchantpay/WebServices-SDK-php_20161013/WebServices-SDK-php-EMP/lib/WSSDK/WSSDK.php";
        // we extend the WSSDK/Model/OrderSubmit class (to add order_language field)
        require_once APPPATH . "vendor/emerchantpay/lotto/LottoWSSDKOrderSubmit.php";
        // we extend the WSSDK/Model/Customer class (to remove mandatory first name and last name fields)
        require_once APPPATH . "vendor/emerchantpay/lotto/LottoWSSDKCustomer.php";
        // extended credit card for previous order ids
        require_once APPPATH . "vendor/emerchantpay/lotto/LottoWSSDKPreviousCreditCard.php";

        try {
            $myWSSDK = new \WSSDK($pdata['accountid'], $pdata['apikey'], str_replace(["https://", "http://"], "", $pdata['endpoint']));

            echo "<br><br>WSSDK:<br>";
            echo json_encode($myWSSDK);
            echo "<br><br>";
        } catch (Exception $exc) {
            echo "<br><br>EXCEPTION:<br>";
            var_dump($exc);
            echo "<br><br>";
        }

        $order = new LottoWSSDKOrderSubmit();
        $order->setOrderReference($whitelabel['prefix'] . ('P') . '123123123');
        //$order->setOrderLanguage(substr($language['code'], 0, 2)); // TODO: check on language addition
        $order->setIpAddress(Lotto_Security::get_IP());
        $order->setCurrency(\WSSDK\CURRENCY::EUR);

        echo "<br><br>LottoWSSDKOrderSubmit:<br>";
        var_dump($order);
        echo "<br><br>";

        $dbc = Model_Emerchantpay_User::find_by_whitelabel_user_id($user['id']);

        echo "<br><br>DBC:<br>";
        var_dump($dbc);
        echo "<br><br>";

        $customer = null;
        $customer_id = null;

        // if customer-emerchant exists
        if ($dbc !== null && count($dbc) > 0) {
            $customer_id = $dbc[0]['customer_id'];
            $order->setCustomerId($dbc[0]['customer_id']);

            // why not $customer object?
            // eMerchant sux, it will not let us pass customer object while customer_id is set up
            // so HOW THE FUCK ARE WE SUPPOSED TO PASS THE customer_email WHICH IS REQUIRED??
            $order->setCustomerEmail($user['email']);
        } else {
            $customer = new LottoWSSDKCustomer();
            $customer->setEmail($user['email']);
            if (!empty($user['name'])) {
                $customer->setFirstName($user['name']);
            }
            if (!empty($user['surname'])) {
                $customer->setLastName($user['surname']);
            }
            if (!empty($user['address_1'])) {
                $customer->setAddressLine1($user['address_1']);
            }
            if (!empty($user['address_2'])) {
                $customer->setAddressLine2($user['address_2']);
            }
            if (!empty($user['city'])) {
                $customer->setCity($user['city']);
            }
            if (!empty($user['state'])) {
                $state = explode('-', $user['state']);
                $customer->setState($state[1]);
            }
            if (!empty($user['country'])) {
                $customer->setCountry($user['country']);
            }
            if (!empty($user['zip'])) {
                $customer->setPostcode($user['zip']);
            }
            if (!empty($user['phone']) && !empty($user['phone_country'])) {
                $customer->setPhone($user['phone']);
            }
        }

        echo "<br><br>LottoWSSDKOrderSubmit:<br>";
        var_dump($order);
        echo "<br><br>";

        echo "<br><br>Customer:<br>";
        var_dump($customer);
        echo "<br><br>";


        $paymentType = new \WSSDK\Model\PaymentType\CreditCard();

        $paymentType->setName("Tester");
        $paymentType->setNumber("4111111111111111");
        $paymentType->setExpiryMonth("01");
        $paymentType->setExpiryYear("19");
        $paymentType->setCVV("123");
        $paymentType->setTransactionType('sale');

        //        if ($val->validated("paymentcc.remember") == 1) {
        //            $paymentType->setRememberCardFlag(1);
        //        }

        echo "<br><br>Payment Type Card:<br>";
        var_dump($paymentType);
        echo "<br><br>";

        $items = [];
        $item = new \WSSDK\Model\Item\OneOffDynamicItem();
        $item->setCode($whitelabel['prefix'] . "_DEPOSIT");
        $item->setName("Deposit");
        $item->setQuantity(1);
        $item->setUnitPrice(\WSSDK\CURRENCY::EUR, 2.00);
        $item->setProductType(\WSSDK\Model\Item\DynamicItem::DIGITAL_PRODUCT);

        $items[] = $item;

        echo "<br><br>Item:<br>";
        var_dump($item);
        echo "<br><br>";

        echo "<br><br>Items:<br>";
        var_dump($items);
        echo "<br><br>";

        // TEST REQUEST (true)
        $test_request = $pdata['test'];

        echo "<br><br>TEST REQ:<br>";
        var_dump($test_request);
        echo "<br><br>";

        $req = $myWSSDK->orderSubmitRequest($order, $test_request);
        if ($customer !== null) {
            // Set customer
            $req->setCustomer($customer);
        }
        // Add models
        $req->setPaymentType($paymentType);

        foreach ($items as $item) {
            $req->addItem($item);
        }

        echo "<br><br>REQ:<br>";
        print_r($req);
        echo "<br><br>";

        $res = $req->send();

        // Get deserialized data from body of request
        $body = $res->getBody();
        $headers = $res->getHeaders();

        echo "<br><br>BODY:<br>";
        var_dump($body);
        echo "<br><br>";

        echo "<br><br>HEADERS:<br>";
        var_dump($headers);
        echo "<br><br>";

        $type = $body->getName();

        echo "<br><br>TYPE:<br>";
        var_dump($type);
        echo "<br><br>";


//        $ecustomer = $myWSSDK->customerRetrieveRequest(\WSSDK\Model\CustomerRetrieve::ByEmailAddress($user['email']), $test_request);
//        $ecustomer = $ecustomer->send();
//
//        $customer_body = $ecustomer->getBody();
//        $customer_headers = $ecustomer->getHeaders();
//
//        echo "<br><br>ECustomer:<br>";
//        var_dump($ecustomer);
//        echo "<br><br>";
//
//        echo "<br><br>Customer body:<br>";
//        var_dump($customer_body);
//        echo "<br><br>";
//
//        echo "<br><br>Customer headers:<br>";
//        var_dump($customer_headers);
//        echo "<br><br>";
    }

    public function action_emerchantcustomer()
    {
        require_once APPPATH . "vendor/emerchantpay/WebServices-SDK-php_20161013/WebServices-SDK-php-EMP/lib/WSSDK/WSSDK.php";
        // we extend the WSSDK/Model/OrderSubmit class (to add order_language field)
        require_once APPPATH . "vendor/emerchantpay/lotto/LottoWSSDKOrderSubmit.php";
        // we extend the WSSDK/Model/Customer class (to remove mandatory first name and last name fields)
        require_once APPPATH . "vendor/emerchantpay/lotto/LottoWSSDKCustomer.php";
        // extended credit card for previous order ids
        require_once APPPATH . "vendor/emerchantpay/lotto/LottoWSSDKPreviousCreditCard.php";

        $user_email = 'tester@local.pl';
        $user_email = 'tunhan030184@gmail.com';

        $pdata = [
            'accountid' => "1213734",
            'apikey' => "nhtBx8Z7IpIYRgX37qzZ",
            'endpoint' => "https://my.emerchantpay.com",
            'test' => 1
        ];

        $myWSSDK = new \WSSDK(
            $pdata['accountid'],
            $pdata['apikey'],
            str_replace(["https://", "http://"], "", $pdata['endpoint'])
        );

        echo "<br><br>WSSDK:<br>";
        var_dump($myWSSDK);
        echo "<br><br>";

        $test_request = ($pdata['test'] == 1 ? true : false);

        $ecustomer = $myWSSDK->customerRetrieveRequest(\WSSDK\Model\CustomerRetrieve::ByEmailAddress($user_email), $test_request);
        $ecustomer = $ecustomer->send(true);

        echo "<br><br>ECUSTOMER:<br>";
        var_dump($ecustomer);
        echo "<br><br>";

        $customer_body = $ecustomer->getBody();

        echo "<br><br>CUSTOMER BODY:<br>";
        var_dump($customer_body);
        echo "<br><br>";

        $customer_headers = $ecustomer->getHeaders();

        echo "<br><br>CUSTOMER HEADERS:<br>";
        var_dump($customer_headers);
        echo "<br><br>";
    }

    /**
     * Update line_count collumn for whitelabel_user_ticket.
     */
    public function action_update_tickets_line_count()
    {
        set_time_limit(0);
        // fetch line_count for every ticket from database.
        $query = DB::query( // inner join - there is no need to update tickets, which don't have lines
            "SELECT t.id, count(t.id) as line_count from whitelabel_user_ticket as t
                INNER JOIN whitelabel_user_ticket_line as l ON t.id = l.whitelabel_user_ticket_id
                GROUP BY t.id"
        );

        // execute query and save as array
        $result = $query->execute()->as_array();

        // check result
        if ($result === null || count($result) === 0 || empty($result[0])) {
            throw new Exception("whitelabel_user_ticket_line table is empty!");
        }

        // ok, update every fetched ticket
        $id = "id";
        $line_count = "line_count";
        foreach ($result as $ticket) {
            // since it's in dev I ommit security aspect
            DB::query(
                "UPDATE whitelabel_user_ticket
                    SET line_count = $ticket[$line_count]
                    WHERE id = $ticket[$id]"
            )
                ->execute();
        }
    }

    public function action_checkcur()
    {
        $time = filemtime(APPPATH . 'vendor/cldr/supplemental/supplementalData.xml');
        $file_name = APPPATH . 'vendor/cldr/currencies-map-' . $time . '.json';
        if (file_exists($file_name)) {
            $map = json_decode(file_get_contents($file_name), true);
            echo "<br><br>MAP OF CURRENCIES:<br>";
            echo json_encode($map);
            echo "<br><br>";
        }
    }

    public function action_emptylastdraws()
    {
        if (\Fuel::$env == \Fuel::PRODUCTION) {
            exit("Dont't do it on production!");
        }
        DB::query(
            "UPDATE lottery SET " .
             "last_date_local = null," .
             "next_date_local = null, next_date_utc = null, additional_data = null,
                    last_numbers = null, last_bnumbers = null WHERE slug != 'gg-world' AND slug != 'gg-world-x' AND slug != 'gg-world-million' AND slug != 'lotto-zambia' AND slug != 'somoslotto-plus'
                    "
        )->execute();
        echo "last draws dates reset complete";
    }

    public function action_changeticketdates()
    {
        if (\Fuel::$env == \Fuel::PRODUCTION) {
            exit("Dont't do it on production!");
        }
        $query = DB::query(
            "SELECT * FROM whitelabel_user_ticket WHERE date_processed IS NULL"
        );
        // execute query and save as array
        $result = $query->execute()->as_array();


        // check result
        if ($result === null || count($result) === 0 || empty($result[0])) {
            echo "tickets not found";
        }

        foreach ($result as $ticket) {
            $draw_date_query = DB::query(
                "SELECT last_date_local FROM lottery WHERE id = {$ticket['lottery_id']}"
            );
            $draw_date_arr = $draw_date_query->execute()->as_array();
            $draw_date = $draw_date_arr[0]['last_date_local'];
            if (isset($draw_date) && $draw_date != null) {
                DB::query(
                    "UPDATE whitelabel_user_ticket
                    SET valid_to_draw = '{$draw_date}', draw_date = '{$draw_date}'
                    WHERE id = {$ticket['id']}"
                )->execute();
                echo $ticket['id'] . " updated";
                echo "<br>";
            }
        }
    }


    public function action_validatewinnings()
    {
        $lottery_id = Input::get('lottery_id') ?? 1;
        // Just change this line below to test another one
        $lottery_instance = $this->get_lottery_instance_by_lottery_id($lottery_id);
        if ($lottery_instance == null) {
            exit("Bad lottery id");
        }
        echo "Validating lottery winnings for lottery $lottery_id";

        $lottery = Model_Lottery::find_by_pk($lottery_id);
        if ($lottery == null) {
            exit("Lottery not found");
        }

        $lottery_classes = Model_Lottery::LOTTERY_CLASSES;
        if (!isset($lottery_classes[$lottery->slug])) {
            exit("Lottery class not found");
        }
        $lottery_instance = new $lottery_classes[$lottery->slug]();

        $lottery = $lottery->to_array();

        $lottery_tickets = DB::select('*')
            ->from('whitelabel_user_ticket')
            ->where('lottery_id', '=', $lottery['id'])
            ->where('paid', '=', 1)
//            ->where('status', '=', 2)
            ->where('date_processed', 'IS NOT', null)->execute();

        $now = (new \DateTime())->format('Y-m-d');
        $type = Model_Lottery_Type::get_lottery_type_for_date($lottery, $now);

        foreach ($lottery_tickets as $ticket) {
            $lines = Model_Whitelabel_User_Ticket_Line::find([
                "where" => [
                    "whitelabel_user_ticket_id" => $ticket['id'],
                ]
            ]);
            $lottery_draw = Model_Lottery_Draw::find([
                "where" => [
                    "lottery_id" => $lottery['id'],
                    "date_local" => $ticket['draw_date']
                ]
            ])[0];
            if ($lottery_draw == null) {
                continue;
            }
            $line_numbers_draw = explode(',', $lottery_draw->numbers);
            $line_numbers_draw = array_map(function ($val) {
                return intval($val);
            }, $line_numbers_draw);

            $line_bnumbers_draw = explode(',', $lottery_draw->bnumbers);
            $line_bnumbers_draw = array_map(function ($val) {
                return intval($val);
            }, $line_bnumbers_draw);

            if ($lottery_draw->additional_data) {
                $additional_data_draw = unserialize($lottery_draw->additional_data);
                $refund_draw = null;
                if (isset($additional_data_draw['refund'])) {
                    $refund_draw = $additional_data_draw['refund'];
                } elseif (isset($additional_data_draw['super'])) {
                    $refund_draw = $additional_data_draw['super'];
                }
            }

            $lottery_instance->set_lottery(
                $lottery,
                1000000,
                new \DateTime(),
                new \DateTime(),
                $line_numbers_draw,
                $line_bnumbers_draw,
                [],
                false,
                false,
                unserialize($lottery_draw->additional_data)
            );

            foreach ($lines as $line) {
                $slip = Model_Whitelabel_User_Ticket_Slip::find_by_pk($line['whitelabel_user_ticket_slip_id']);
                if ($slip['additional_data']) {
                    $additional_data = unserialize($slip['additional_data']);
                    $refund = null;
                    if (isset($additional_data['refund'])) {
                        $refund = $additional_data['refund'];
                    } elseif (isset($additional_data_draw['super'])) {
                        $refund = $additional_data_draw['super'];
                    }
                }
                $line_numbers_text = explode(',', $line['numbers']);
                $line_numbers = array_map(function ($val) {
                    return intval($val);
                }, $line_numbers_text);

                $line_bnumbers_text = explode(',', $line['bnumbers']);
                $line_bnumbers = array_map(function ($val) {
                    return intval($val);
                }, $line_bnumbers_text);

                $match_n = 0;
                $match_b = 0;
                $match_others_temp = 0;

                $match_n = $lottery_instance->match_n($match_n, $line_numbers);
                $match_b = $lottery_instance->match_b($match_b, $type, $line_bnumbers, $line_numbers);
                $match_others = $lottery_instance->match_others($match_others_temp, $line);
                if ($lottery_instance->check_match($match_n, $match_b, $match_others)) {
                    $type_data = Model_Lottery_Type_Data::find(
                        [
                            'where' => [
                                'lottery_type_id' => $type['id'],
                            ],
                            'order_by' => 'id'
                        ]
                    );
                    foreach ($type_data as $winkey => $wintype) {
                        $wintype_check = $lottery_instance->get_win_type(
                            $type,
                            $wintype,
                            $match_n,
                            $match_b,
                            $match_others
                        );

                        if ($wintype_check !== null) {
                            if ($line->status != 1) {
                                echo "Line {$line['id']} should win prize {$wintype['id']} ({$wintype['match_n']} + {$wintype['match_b']}) <br>";
                                echo "--- Line numbers: $line->numbers <br>";
                                echo "--- Line bonus numbers: $line->bnumbers <br>";
                                echo "--- Line additional numbers: $refund <br>";
                                echo "---  Draw numbers: $lottery_draw->numbers <br>";
                                echo "---  Draw bonus numbers: $lottery_draw->bnumbers <br>";
                                echo "---  Draw additional numbers: $refund_draw <br> <br> <br>";
                            }
                        }
                    }
                }
            }
        }
        die("Script ended");
    }

    public function action_buywinnings()
    {
        if (\Fuel::$env == \Fuel::PRODUCTION) {
            exit("Dont't do it on production!");
        }
        $user_id = Input::get('user') ?? 1;

        $user = Model_Whitelabel_User::find_by_pk($user_id); // get whitelabel_user cache instance
        $is_user = Lotto_Settings::getInstance()->get("is_user"); // get user status (is logged in)
        $whitelabel = Model_Whitelabel::find_by_pk(1)->to_array();
        $emerchant_data = null;
        $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);
        $basket = [];
        $currency = Model_Currency::find_by_pk(1);
        $total_price = 0;
        $query = DB::query(
            "SELECT * FROM lottery WHERE is_enabled = 1"
        );
        $l_num = 0;
        // execute query and save as array
        $result = $query->execute()->as_array();
        $basket = [];
        foreach ($result as $l) {
            $basket_n = [
                0 => $l['id'],
                1 => []
            ];
            array_push($basket, $basket_n);

            $lottery = $lotteries['__by_id'][$l['id']];
            $lottery_type = null;
            if (Lotto_Helper::is_lottery_closed($lottery, null, $whitelabel)) {
                // TODO: check if draw has been moved in get_lottery_next_draw
                $lottery_type = Model_Lottery_Type::get_lottery_type_for_date($lottery, Lotto_Helper::get_lottery_next_draw($lottery, 2)->format('Y-m-d'));
            } else {
                $lottery_type = Model_Lottery_Type::get_lottery_type_for_date($lottery, Lotto_Helper::get_lottery_next_draw($lottery)->format('Y-m-d'));
            }
            $type_data = Model_Lottery_Type_Data::get_lottery_type_data($lottery);
            if ($lottery_type['bcount'] > 0) {
                $type_data[] = [
                    'match_n' => 1,
                    'match_b' => 1,
                    'additional_data' => ''
                ];
//                echo "<pre>";print_r($type_data);echo "</pre>";die();
            }
            $last_numbers = explode(',', $l['last_numbers']);
            $last_bnumbers = explode(',', $l['last_bnumbers']);
            $t_num = 0;
            foreach ($type_data as $w) {
                $numbers = [];
                $bnumbers = [];

                $additional_data = unserialize($w['additional_data']);
                if (isset($additional_data['refund'])) {
//                    continue;
                }

                // Winnings numbers
                for ($i = 0; $i < $w['match_n']; $i++) {
                    $unique = false;

                    do {
                        $rand = $last_numbers[$i];
                        if (!in_array($rand, $numbers)) {
                            array_push($numbers, intval($rand));
                            $unique = true;
                        }
                    } while (!$unique);
                }
                // Extra numbers
                $extra = 0;
                if ($lottery_type['bcount'] == 0 && $lottery_type['bextra'] > 0 && $w['match_b'] > 0) {
                    for ($i = 0; $i < $w['match_b']; $i++) {
                        $unique = false;

                        do {
                            $rand = $last_bnumbers[$extra];
                            if (!in_array($rand, $numbers)) {
                                array_push($numbers, intval($rand));
                                $unique = true;
                            }
                        } while (!$unique);
                        $extra++;
                    }
                }

                // Not wining numbers
                for ($i = $w['match_n'] + $extra; $i < $lottery_type['ncount']; $i++) {
                    $unique = false;

                    do {
                        $rand = rand(1, $lottery_type['nrange']);
                        if (!in_array($rand, $numbers) && !in_array($rand, $last_numbers)) {
                            array_push($numbers, intval($rand));
                            $unique = true;
                        }
                    } while (!$unique);
                }
                if ($lottery_type['bcount'] == 0 && $lottery_type['bextra'] > 0 && $w['match_b'] > 0) {
//                    var_dump($w);
                } else {
                    // Winning bonus numbers
                    for ($i = 0; $i < $w['match_b']; $i++) {
                        $unique = false;
                        do {
                            $rand = $last_bnumbers[$i];
                            if (!in_array($rand, $bnumbers)) {
                                array_push($bnumbers, intval($last_bnumbers[$i]));
                                $unique = true;
                            }
                        } while (!$unique);
                    }

                    // Not winning bonus numbers
                    for ($i = $w['match_b']; $i < $lottery_type['bcount']; $i++) {
                        $unique = false;

                        do {
                            $rand = rand(1, $lottery_type['brange']);
                            if (!in_array($rand, $bnumbers) && !in_array($rand, $last_bnumbers)) {
                                array_push($bnumbers, intval($rand));
                                $unique = true;
                            }
                        } while (!$unique);
                    }
                }


                $basket1 = [
                    0 => $numbers,
                    1 => $bnumbers
                ];
                array_push($basket[$l_num][1], $basket1);

                $t_num++;
            }
            $l_num++;
        }
//        echo "<pre>";print_r($basket);echo "</pre>";die();
        try {
            DB::start_transaction();
            // little checking
            foreach ($basket as $item) {
                $lottery = $item['lottery'];
                $lines = $item['lines'];
                if (!isset($lottery)) {
                    throw new Exception("Unknown lottery.");
                }

                if ($lines === null || count($lines) == 0) {
                    continue;
                }
                $lottery = $lotteries['__by_id'][$lottery];
                $lottery_type = null;
                if (Lotto_Helper::is_lottery_closed($lottery, null, $whitelabel)) {
                    // TODO: check if draw has been moved in get_lottery_next_draw
                    $lottery_type = Model_Lottery_Type::get_lottery_type_for_date($lottery, Lotto_Helper::get_lottery_next_draw($lottery, 2)->format('Y-m-d'));
                } else {
                    $lottery_type = Model_Lottery_Type::get_lottery_type_for_date($lottery, Lotto_Helper::get_lottery_next_draw($lottery)->format('Y-m-d'));
                }

                $total_price = $total_price;


                foreach ($lines as $line) {
                    $numbers = $line[0];
                    $bnumbers = $line[1];
                    $nums = [];
                    foreach ($numbers as $number) {
                        $nums[$number] = 1;
                    }
                    $bnums = [];
                    if ($lottery_type['bcount'] > 0) {
                        foreach ($bnumbers as $number) {
                            $bnums[$number] = 1;
                        }
                    }

                    $numc = array_unique(array_keys($nums));
                    $bnumc = array_unique(array_keys($bnums));

                    if (
                        !(
                        count($numc) == $lottery_type['ncount'] &&
                        ($lottery_type['bextra'] == 0 &&
                            count($bnumc) == $lottery_type['bcount']) ||
                        ($lottery_type['bextra'] > 0 && count($bnumc) == 0)
                        )
                    ) {
                        throw new Exception("Incorrect amount of unique numbers. [" . count($numc) . " " . count($bnumc) . "]");
                    }

                    if (
                        count($numbers) != $lottery_type['ncount'] ||
                        count($bnumbers) != $lottery_type['bcount']
                    ) {
                        throw new Exception("Incorrect amount of numbers. [" . count($numbers) . " " . count($bnumbers) . "]");
                    }

                    foreach ($numbers as $number) {
                        if (
                            intval($number) < 1 ||
                            intval($number) > intval($lottery_type['nrange'])
                        ) {
                            throw new Exception("Number out of range. [" . $number . "]");
                        }
                    }
                    foreach ($bnumbers as $bnumber) {
                        if (
                            intval($bnumber) < 1 ||
                            intval($bnumber) > intval($lottery_type['brange'])
                        ) {
                            throw new Exception("Bonus number out of range. [" . $bnumber . "]");
                        }
                    }
                }
            }


            // let's start transaction
            $transaction = Model_Whitelabel_Transaction::forge();
            $transaction->set([
                'token' => Lotto_Security::generate_transaction_token($whitelabel['id']),
                'whitelabel_id' => $whitelabel['id'],
                'whitelabel_user_id' => $user['id'],
                'currency_id' => $currency['id'],
                'amount' => round($total_price, 2),
                'amount_usd' => round($total_price * round(1 / $currency['rate'], 4), 2),
                'amount_manager' => round($total_price * round(1 / $currency['rate'], 4), 2),
                'date' => DB::expr("NOW()"),
                'status' => Helpers_General::STATUS_TRANSACTION_PENDING,
                'type' => Helpers_General::TYPE_TRANSACTION_PURCHASE // payment, not depo
            ]);
            $transaction->save();

            $income_total = $income_usd_total = $cost_total = $cost_usd_total = $margin_total = $margin_usd_total = 0;
            foreach ($basket as $item) {
                $lottery = $item['lottery'];

                $lines = $item['lines'];
                $lines_count = count($lines);


                if (!isset($lotteries['__by_id'][$lottery])) {
                    throw new Exception("Unknown lottery (2).");
                }

                if (!$lines_count) {
                    continue;
                }
                $lottery = $lotteries['__by_id'][$lottery];

                $ticket = Model_Whitelabel_User_Ticket::forge();
                $ticket_draw_date = null;
                if (Lotto_Helper::is_lottery_closed($lottery, null, $whitelabel)) {
                    // TODO: adjust next draw on lottery changes
                    $ticket_draw_date = Lotto_Helper::get_lottery_next_draw($lottery, 2);
                } else {
                    $ticket_draw_date = Lotto_Helper::get_lottery_next_draw($lottery);
                }

                $type = Model_Lottery_Type::get_lottery_type_for_date($lottery, $ticket_draw_date->format(Helpers_Time::DATETIME_FORMAT));
                //$type = Model_Lottery_Type::find_by_pk($lottery['lottery_type_id']);
                if ($type === null) {
                    throw new Exception('Helper - No lottery type.');
                }

                $itm_price = 1;

                $itm_price_local = Lotto_Helper::get_user_price($lottery);
                $itm_price_usd = Helpers_Currency::convert_to_USD($itm_price, $currency['code']);

                $price = round($itm_price * $lines_count, 4);
                $price_usd = round($itm_price_usd * $lines_count, 4);
                $price_local = round($itm_price_local * $lines_count, 4);

                $model = $lottery['model'];

                $is_insured = false;
                $tier = 0;
                if (
                    $model == Helpers_General::LOTTERY_MODEL_MIXED &&
                    Lotto_Helper::should_insure($lottery, $lottery['tier'], $lottery['volume'])
                ) {
                    $is_insured = true;
                    $tier = $lottery['tier'];
                }

                $calc_cost = Lotto_Helper::get_price($lottery, $lottery['model'], $lottery['tier'], $lottery['volume']);

                $cost_local = round(($calc_cost[0] + $calc_cost[1]) * $lines_count, 4);
                $cost_usd = Helpers_Currency::convert_to_USD($cost_local, $lottery['currency']);
                $cost = Helpers_Currency::convert_to_EUR($cost_local, $lottery['currency']);

                $income_local = round($price_local - $cost_local, 4);
                $income_usd = round($price_usd - $cost_usd, 4);
                $income = round($price - $cost, 4);

                $income_value = $lottery['income'];
                $income_type = $lottery['income_type'];

                $margin_value = $whitelabel['margin'];

                $wl_margin = round($whitelabel['margin'] / 100, 4);

                $margin_local = round($income_local * $wl_margin, 2);
                $margin_usd = round($income_usd * $wl_margin, 2);
                $margin = round($income * $wl_margin, 2);
                ///////// end of price calculations ///////////
                $ticket->set([
                    'token' => Lotto_Security::generate_ticket_token($whitelabel['id']),
                    'whitelabel_transaction_id' => $transaction->id,
                    'whitelabel_id' => $whitelabel['id'],
                    'whitelabel_user_id' => $user['id'],
                    'lottery_id' => $item['lottery'],
                    'lottery_type_id' => $type['id'],
                    'currency_id' => $currency['id'],
                    'draw_date' => $ticket_draw_date->format(Helpers_Time::DATETIME_FORMAT),
                    'valid_to_draw' => $ticket_draw_date->format(Helpers_Time::DATETIME_FORMAT),
                    'amount' => round($price, 2),
                    'amount_usd' => round($price_usd, 2),
                    'date' => DB::expr("NOW()"),
                    'status' => Helpers_General::TICKET_STATUS_PENDING,
                    'paid' => Helpers_General::TICKET_PAID,
                    'payout' => 0,
                    'model' => $lottery['model'],
                    'amount_local' => round($price_local, 2),
                    'is_insured' => $is_insured,
                    'tier' => $tier,
                    'cost_local' => round($cost_local, 2),
                    'cost_usd' => $cost_usd,
                    "cost" => $cost,
                    "income_local" => round($income_local, 2),
                    "income_usd" => round($income_usd, 2),
                    "income" => round($income, 2),
                    "income_value" => $income_value,
                    "income_type" => $income_type,
                    "margin_value" => $margin_value,
                    "margin_local" => $margin_local,
                    "margin_usd" => 0,
                    "margin" => 0,
                    "ip" => Lotto_Security::get_IP(),
                    "line_count" => count($lines),
                ]);

                $cost_total = round($cost_total + $cost, 4);
                $cost_usd_total = round($cost_usd_total + $cost_usd, 4);
                $margin_total = round($margin_total + $margin, 4);
                $margin_usd_total = round($margin_usd_total + $margin_usd, 4);
                $income_total = round($income_total, $income + 4);
                $income_usd_total = round($income_usd_total + $income_usd, 4);

                $ticket->save();

                $whitelabel_lottery = Model_Whitelabel_Lottery::find_for_whitelabel_and_lottery(
                    $ticket['whitelabel_id'],
                    $ticket['lottery_id']
                )[0];

                $slip = Model_Whitelabel_User_Ticket_Slip::forge();
                $slip->set([
                    'whitelabel_user_ticket_id' => $ticket->id,
                    'whitelabel_lottery_id' => $whitelabel_lottery['id']
                ]);
                $slip->save();

                foreach ($lines as $line) {
                    $numbers = $line[0];
                    $bnumbers = $line[1];

                    $ticket_line = Model_Whitelabel_User_Ticket_Line::forge();
                    $ticket_line->set([
                        'whitelabel_user_ticket_id' => $ticket->id,
                        'whitelabel_user_ticket_slip_id' => $slip->id,
                        'numbers' => implode(',', $numbers),
                        'bnumbers' => implode(',', $bnumbers),
                        "amount_local" => round($itm_price_local, 2),
                        'amount' => round($itm_price, 2),
                        'amount_usd' => round($itm_price_usd, 2),
                        'status' => Helpers_General::TICKET_STATUS_PENDING,
                        'payout' => 0
                    ]);
                    $ticket_line->save();
                }
            }
            $transaction->set([
                "cost" => round($cost_total, 2),
                "cost_usd" => round($cost_usd_total, 2),
                "income" => round($income_total, 2),
                "income_usd" => round($income_usd_total, 2),
                "margin" => round($margin_total, 2),
                "margin_usd" => round($margin_usd_total, 2)
            ]);



            Session::set("transaction", $transaction->id);

            DB::commit_transaction();

            if ($user['balance'] >= $transaction->amount) {
                $transaction->set([
                    'payment_method_type' => Helpers_General::PAYMENT_TYPE_BALANCE,
                ]);
                $transaction->save();

                $accept_transaction_result = Lotto_Helper::accept_transaction(
                    $transaction,
                    null,
                    null,
                    $whitelabel
                );

                // Now transaction returns result as INT value and
                // we can redirect user to fail page or success page
                // or simply inform system about that fact
                if ($accept_transaction_result === Forms_Transactions_Accept::RESULT_WITH_ERRORS) {
                    ;
                }
            } else {
                echo "no balance";
            }
        } catch (Exception $e) {
            DB::rollback_transaction();

            var_dump($e);
        }

        echo "Ticket bought";
        return true;
    }

    public function action_updatereintegro()
    {
        if (\Fuel::$env == \Fuel::PRODUCTION) {
            exit("Dont't do it on production!");
        }
        $match_reintegro = Input::get('match_reintegro') ?? 1;
        $ball_name = Input::get('ball_name') ?? 'refund';

        $query = DB::query(
            "SELECT lottery_id,additional_data FROM lottery_type WHERE additional_data IS NOT NULL"
        );
        // execute query and save as array
        $ids = [];
        $result = $query->execute()->as_array();
        foreach ($result as $l) {
            $additional_data = unserialize($l['additional_data']);
            if (isset($additional_data[$ball_name])) {
                array_push($ids, intval($l['lottery_id']));
            }
        }
        $in = '(' . implode(',', $ids) . ')';

        $query = DB::query(
            "SELECT id, lottery_id FROM whitelabel_user_ticket WHERE date_processed IS NULL AND lottery_id IN " . $in
        );
        $tickets = $query->execute()->as_array();


        // check result
        if ($tickets === null || count($tickets) === 0 || empty($tickets[0])) {
            echo "tickets not found";
            die();
        }

        foreach ($tickets as $t) {
            $ticket_id = intval($t['id']);
            $lottery = Model_Lottery::find_by_pk(intval($t['lottery_id']));
            $additional_data = unserialize($lottery['additional_data']);
            $refund = $additional_data[$ball_name];
            if ($match_reintegro == false) {
                $refund > 0 ? $refund++ : $refund--;
            }
            $slip = Model_Whitelabel_User_Ticket_Slip::find([
                'where' => ["whitelabel_user_ticket_id" => $ticket_id],
                "order_by" => ["id" => "desc"]
            ]);
            foreach ($slip as $s) {
                $s->additional_data = serialize([$ball_name => $refund]);
                $s->save();
            }
        }
        echo "reintegro updated";
        return true;
    }

    public function action_magictickets()
    {
        if (\Fuel::$env == \Fuel::PRODUCTION) {
            exit("Dont't do it on production!");
        }
        $this->action_buywinnings();
        sleep(2);
        $this->action_changeticketdates();
        sleep(2);
        if (Input::get('reintegro') == 1) {
            $this->action_updatereintegro();
            sleep(1);
        }
        $this->action_emptylastdraws();
        sleep(1);
        echo "magic tickets done";
    }

    /**
     * Function to update all deleted users with null value in date_delete field
     */
    public function action_updateemptydeletedates()
    {
        $users = Model_Whitelabel_User::find([
            'where' => [
                'is_deleted' => 1,
                'date_delete' => null
            ]
        ]);

        if ($users === null) {
            echo "There is no users to update.";
            exit();
        }

        echo "Number of users to update: " . count($users) . "<br>";

        $now = new DateTime("now", new DateTimeZone("UTC"));

        foreach ($users as $user) {
            echo "UserID: ", $user['id'] . " and email: " . $user['email'] . "<br>";

            $set = [
                'date_delete' => $now->format("Y-m-d H:i:s")
            ];
            $user->set($set);
            $user->save();
        }
    }


    public function action_import_to_customer()
    {
        set_time_limit(0);

        \Fuel\Core\Event::register('user_register', 'Events_User_Register::handle');
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            echo "Whitelabel: {$whitelabel['id']}: <br>";
            $customer_api_plugin = Model_Whitelabel_Plugin::get_plugin_by_name($whitelabel['id'], 'customer-api');
            if (isset($customer_api_plugin['is_enabled']) && $customer_api_plugin['is_enabled'] == true) {
                $users = Model_Whitelabel_User::find([
                    "where" => [
                        "whitelabel_id" => $whitelabel['id']
                    ]
                ]);
                foreach ($users as $user) {
                    if ($user->is_active) {
                        $currency = Model_Currency::find_by_pk($user->currency_id);
                        $languages = Model_Language::get_all_languages();
                        $language_code = array_values(array_filter($languages, function ($v) use ($user) {
                            if ($user->language_id == $v['id']) {
                                return $v['code'];
                            }
                        }));
                        \Fuel\Core\Event::trigger('user_register', [
                            'whitelabel_id' => $whitelabel->id,
                            'user_id' => $user->id,
                            'plugin_data' => [
                                "created_at" => time(),
                                'email' => $user->email,
                                'currency' => $currency->code ?? "",
                                'is_active' => $user->is_active,
                                'is_confirmed' => $user->is_confirmed,
                                'is_deleted' => $user->is_deleted,
                                'name' => $user->name,
                                'surname' => $user->surname,
                                'date_register' => $user->date_register,
                                'balance' => $user->balance,
                                'register_ip' => $user->register_ip,
                                'last_ip' => $user->last_ip,
                                'last_active' => $user->last_active,
                                'last_update' => $user->last_update,
                                'last_country' => $user->last_country,
                                'register_country' => $user->register_country,
                                'birthdate' => $user->birthdate,
                                'phone' => $user->phone,
                                'phone_country' => $user->phone_country,
                                'country_code' => $user->country,
                                'city' => $user->city,
                                'region' => $user->state,
                                'address_1' => $user->address1,
                                'address_2' => $user->address2,
                                'zipcode' => $user->zip,
                                'gender' => $user->gender,
                                'language' => $language_code[0]['code']
                            ],
                        ]);
                        echo "$user->email added. <br>";
                    }
                }
            }
            echo "DONE!!!<br>";
        }
    }

    /** @deprecated  */
    public function action_import_to_mautic_empty()
    {
        set_time_limit(0);
        $offset = Input::get('offset') ?? 0;
        \Fuel\Core\Event::register('user_register', 'Events_User_Register::handle');
        $whitelabels = Model_Whitelabel::find();
        foreach ($whitelabels as $whitelabel) {
            echo "Whitelabel: {$whitelabel['id']}: <br>";
            $mautic_api_plugin = Model_Whitelabel_Plugin::get_plugin_by_name($whitelabel['id'], 'mautic-api');
            if (isset($mautic_api_plugin['is_enabled']) && $mautic_api_plugin['is_enabled'] == true) {
                $query = DB::query("SELECT whitelabel_user.* FROM whitelabel_user WHERE whitelabel_user.whitelabel_id = {$whitelabel->id} AND NOT EXISTS ( SELECT * FROM whitelabel_plugin_user WHERE whitelabel_plugin_user.whitelabel_user_id = whitelabel_user.id )");
                $users = $query->execute();
                if (empty($users)) {
                    echo "Mautic import completed...";
                    return;
                }
                foreach ($users as $user) {
                    $currency = Model_Currency::find_by_pk($user['currency_id']);
                    $languages = Model_Language::get_all_languages();
                    $language_code = array_values(array_filter($languages, function ($v) use ($user) {
                        if ($user['language_id'] == $v['id']) {
                            return $v['code'];
                        }
                    }));
                    \Fuel\Core\Event::trigger('user_register', [
                            'whitelabel_id' => $whitelabel->id,
                            'user_id' => $user['id'],
                            'plugin_data' => [
                                "created_at" => time(),
                                'email' => $user['email'],
                                'currency' => $currency['code'] ?? "",
                                'is_active' => $user['is_active'],
                                'is_confirmed' => $user['is_confirmed'],
                                'is_deleted' => $user['is_deleted'],
                                'date_register' => $user['date_register'],
                                'balance' => $user['balance'],
                                'register_ip' => $user['register_ip'],
                                'last_ip' => $user['last_ip'],
                                'last_active' => $user['last_active'],
                                'last_update' => $user['last_update'],
                                'last_country' => $user['last_country'],
                                'register_country' => $user['register_country'],
                                'firstname' => $user['name'],
                                'lastname' => $user['surname'],
                                'date_of_birth' => $user['birthdate'],
                                'phone' => $user['phone'],
                                'phone_country_code' => $user['phone_country'],
                                'country_code' => $user['country'],
                                'city' => $user['city'],
                                'region' => $user['state'],
                                'address_1' => $user['address_1'],
                                'address_2' => $user['address_2'],
                                'zipcode' => $user['zip'],
                                'gender' => $user['gender'] == 1 ? "male" : "female",
                                'language' => $language_code[0]['code'],
                                'last_purchase_date' => $user['last_purchase_date'],
                                'last_purchase_amount_manager' => $user['last_purchase_amount_manager']
                            ],
                        ]);
                    echo "{$user['email']} added. <br>";
                }
            }
            echo "DONE!!!<br>";
        }
    }

    public function action_check_neteller_con()
    {
//        $locale = 'pl_PL'; // przykładowo
//        putenv('LC_ALL=' . $locale . '.utf8');
//        setlocale(LC_ALL, $locale . '.utf8');
//
//        echo bcdiv("5,33", "10000", 10);
//
//        die();

        $in_subtype = 4;    // Work
//        $in_subtype = 3;    // Local

        $whitelabel = Model_Whitelabel::find_by_pk(1);
        $whitelabel_payment_methods_without_currency = Model_Whitelabel_Payment_Method::get_payment_methods_for_whitelabel($whitelabel);

        //var_dump($payment_methods);

        $whitelabel_payment_methods = Lotto_Helper::get_whitelabel_payment_methods_for_language(
            $whitelabel,
            $whitelabel_payment_methods_without_currency
        );
        //var_dump($methods);

        $whitelabel_payment_methods_indexed = [];
        if ($whitelabel_payment_methods !== null) {
            $whitelabel_payment_methods_indexed = array_values($whitelabel_payment_methods);
        }
        //var_dump($kmethods);
        echo "<br><br>";
        $subtype = Model_Whitelabel_Payment_Method::find_by_pk($whitelabel_payment_methods_indexed[intval($in_subtype) - 1]['id']);
        var_dump($subtype);


        $pdata = unserialize($subtype['data']);
        echo "<br>pdata:";
        var_dump($pdata);

        $neteller_url = "https://";
        if (!empty($pdata['test'])) {
            $neteller_url .= 'test.';
        }
        $neteller_url .= "api.neteller.com/v1/oauth2/token?grant_type=client_credentials";

        echo "<br><br>URL:";
        var_dump($neteller_url);

        $user_pwd = $pdata['app_client_id'] . ':' . $pdata['app_client_secret'];

        echo "<br><br>USERPWD:";
        var_dump($user_pwd);

        $ssl_verifypeer = 2;
        $ssl_verifyhost = 2;

        echo "<br><br>ENV:<br>";
        echo \Fuel::$env . "<br><br>";


        if (\Fuel::$env == \Fuel::DEVELOPMENT || \Fuel::$env == \Fuel::STAGING) {
            $ssl_verifypeer = 0;
            $ssl_verifyhost = 0;
        }

        echo "<br><br>SSLVERIFYPEER i SSLVERIFYHOST:";
        var_dump($ssl_verifypeer);
        var_dump($ssl_verifyhost);

//        die();

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $neteller_url);
        curl_setopt($ch, CURLOPT_USERPWD, $user_pwd);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type:application/json",
            "Cache-Control:no-cache"
        ]);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $ssl_verifyhost);

        $response = curl_exec($ch);

        echo "<br><br>RESPONSE:";
        var_dump($response);

        if ($response === false) {
            curl_close($ch);
            exit(_("Bad request! Please contact us!"));
        }
        curl_close($ch);
    }

    /**
     *
     */
    public function action_check_system_currencies()
    {
        set_time_limit(0);

        $i = 0;

        $url_text = "https://openexchangerates.org/api/latest.json?app_id=8db4a049671f4d7aa06aa0973124c9c9";

        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url_text);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $ssl_verifypeer = 2;
            $ssl_verifyhost = 2;
            if (Helpers_General::is_development_env()) {
                $ssl_verifypeer = 0;
                $ssl_verifyhost = 0;
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $ssl_verifyhost);

            $response_json = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($response_json);

            foreach ($response->rates as $key => $value) {
                $dbcur = Model_Currency::find_by_code($key);
                if ($dbcur === null || count($dbcur) === 0) {
                    $i++;
                    echo "Currency: " . $key . " NOT found<br>";
                }
            }
        } catch (Exception $e) {
            echo "Exception";
            var_dump($e->getLine());
            var_dump($e->getCode());
            var_dump($e->getMessage());
        }

        echo "<br>Lack of <b>" . $i . "</b> currencies in the system!";

        Cache::delete('model_currency.allcurrencies');
    }

    /** @deprecated */
    public function action_update_new_user_fields()
    {
        set_time_limit(0);
        $fileLoggerService = Container::get(FileLoggerService::class);
//        $offset = Input::get('offset') ?? 0;

        $path = APPPATH . '/tmp_offset_new_user_fields_db_update.txt';
        if (file_exists($path)) {
            $offset = intval(file_get_contents($path));
        } else {
            $offset = 0;
        }
        \Fuel\Core\Event::register('user_edit_profile', 'Events_User_Edit_Profile::handle');
        $users = Model_Whitelabel_User::find([
            "limit" => 100,
            "offset" => $offset,
            "order_by" => ["id" => "asc"]
        ]);
        if ($users) {
            foreach ($users as $user) {
                $sale_status = 0;
                $first_deposit_not_completed = Model_Whitelabel_Transaction::find([
                    'where' => [
                        'whitelabel_user_id' => $user->id,
                        'status' => 0,
                        'type' => 1
                    ],
                    'limit' => 1,
                    'orderby' => 'asc'
                ]);
                if (isset($first_deposit_not_completed[0]->amount_manager)) {
                    $first_deposit_not_completed_amount = $first_deposit_not_completed[0]->amount_manager;
                    if ($first_deposit_not_completed_amount > 0) {
                        $sale_status = 1;
                    }
                }

                $first_deposit_amount = 0;
                $first_transaction = Model_Whitelabel_Transaction::find([
                    'where' => [
                        'whitelabel_user_id' => $user->id,
                        'status' => 1,
                        'type' => 1
                    ],
                    'limit' => 1,
                    'orderby' => 'asc'
                ]);
                if (isset($first_transaction[0]->amount_manager)) {
                    $first_deposit_amount = $first_transaction[0]->amount_manager;
                }

                $total_deposit = 0;
                $total_deposit_transactions = DB::query("SELECT SUM(amount_manager) as sum FROM whitelabel_transaction WHERE whitelabel_user_id = {$user->id} AND status = 1 and type = 1")->execute();
                if (isset($total_deposit_transactions[0]['sum'])) {
                    $total_deposit = $total_deposit_transactions[0]['sum'];
                    if ($total_deposit > 0) {
                        $sale_status = 2; // deposited
                    }
                }

                $total_withdrawal = 0;
                $total_withdrawal_transactions = DB::query("SELECT SUM(amount_manager) as sum FROM withdrawal_request WHERE whitelabel_user_id = {$user->id} AND status = 1")->execute();
                if (isset($total_withdrawal_transactions[0]['sum'])) {
                    $total_withdrawal = $total_withdrawal_transactions[0]['sum'];
                }

                $first_purchase_not_completed = Model_Whitelabel_Transaction::find([
                    'where' => [
                        'whitelabel_user_id' => $user->id,
                        'status' => 0,
                        'type' => 0
                    ],
                    'limit' => 1,
                    'orderby' => 'asc'
                ]);
                if (isset($first_purchase_not_completed[0]->amount_manager)) {
                    $first_purchase_not_completed_amount = $first_purchase_not_completed[0]->amount_manager;
                    if ($first_purchase_not_completed_amount > 0) {
                        $sale_status = 3; // starte purchase
                    }
                }

                $total_purchases = 0;
                $total_purchases_transactions = DB::query("SELECT SUM(amount_manager) as sum FROM whitelabel_transaction WHERE whitelabel_user_id = {$user->id} AND status = 1 and type = 0")->execute();
                if (isset($total_purchases_transactions[0]['sum'])) {
                    $total_purchases = $total_purchases_transactions[0]['sum'];
                    if ($total_purchases > 0) {
                        $sale_status = 4; // purchased
                    }
                }

                $total_net_income = 0;
                $total_net_income_transactions = DB::query("SELECT SUM(income_manager) as sum FROM whitelabel_user_ticket WHERE whitelabel_user_id = {$user->id}")->execute();
                if (isset($total_net_income_transactions[0]['sum'])) {
                    $total_net_income = $total_net_income_transactions[0]['sum'];
                }

                $last_deposit_date = null;
                $last_deposit_amount = 0;
                $last_deposit_date_transaction = Model_Whitelabel_Transaction::find([
                    'where' => [
                        'whitelabel_user_id' => $user->id,
                        'status' => 1,
                        'type' => 1
                    ],
                    'limit' => 1,
                    'orderby' => 'desc'
                ]);
                if (isset($last_deposit_date_transaction[0]->amount_manager) && isset($last_deposit_date_transaction[0]->date)) {
                    $last_deposit_date = $last_deposit_date_transaction[0]->date;
                    $last_deposit_amount = $last_deposit_date_transaction[0]->amount_manager;
                }

                $last_purchase_date = null;
                $last_purchase_amount_manager = 0;
                $last_purchase_transaction = Model_Whitelabel_Transaction::find([
                    'where' => [
                        'whitelabel_user_id' => $user->id,
                        'status' => 1,
                        'type' => 0
                    ],
                    'limit' => 1,
                    'orderby' => 'desc'
                ]);
                if (isset($last_purchase_transaction[0]->amount_manager) && isset($last_purchase_transaction[0]->date)) {
                    $last_purchase_date = $last_purchase_transaction[0]->date;
                    $last_purchase_amount_manager = $last_purchase_transaction[0]->amount_manager;
                }

                $net_winnings = 0;
                $net_winnings_transactions = DB::query("SELECT SUM(prize_net_manager) as sum FROM whitelabel_user_ticket WHERE whitelabel_user_id = {$user->id}")->execute();
                if (isset($net_winnings_transactions[0]['sum'])) {
                    $net_winnings = $net_winnings_transactions[0]['sum'];
                }

                $costs = 0;
                $costs_transactions = DB::query("SELECT SUM(cost_manager) as sum FROM whitelabel_user_ticket WHERE whitelabel_user_id = {$user->id}")->execute();
                if (isset($costs_transactions[0]['sum'])) {
                    $costs = $costs_transactions[0]['sum'];
                }

                $pnl = $total_net_income - $net_winnings;

                $data = [
                    'first_deposit_amount_manager' => $first_deposit_amount,
                    'total_deposit_manager' => $total_deposit,
                    'total_withdrawal_manager' => $total_withdrawal,
                    'total_purchases_manager' => $total_purchases,
                    'total_net_income_manager' => $total_net_income,
                    'last_purchase_date' => $last_purchase_date,
                    'last_deposit_date' => $last_deposit_date,
                    'last_deposit_amount_manager' => $last_deposit_amount,
                    'last_purchase_amount_manager' => $last_purchase_amount_manager,
                    'net_winnings_manager' => $net_winnings,
                    'sale_status' => $sale_status,
                    'pnl_manager' => $pnl,
                ];
                $user->set($data);
                $user->save();
                \Fuel\Core\Event::trigger('user_edit_profile', [
                    'whitelabel_id' => $user->whitelabel_id,
                    'user_id' => $user->id,
                    'plugin_data' => [
                        'first_deposit_amount_manager' => $first_deposit_amount,
                        'total_deposit_manager' => $total_deposit,
                        'total_withdrawal_manager' => $total_withdrawal,
                        'total_purchases_manager' => $total_purchases,
                        'total_net_income_manager' => $total_net_income,
                        'last_purchase_date' => strtotime($last_purchase_date),
                        'last_deposit_date' => strtotime($last_deposit_date),
                        'last_deposit_amount_manager' => $last_deposit_amount,
                        'last_purchase_amount_manager' => $last_purchase_amount_manager,
                        'net_winnings_manager' => $net_winnings,
                        'sale_status' => $sale_status,
                        'pnl_manager' => $pnl,
                    ],
                ]);
                echo "$user->email updated. <br>";
                try {
                    file_put_contents($path, $user->id);
                } catch (Exception $e) {
                    $fileLoggerService->error("Problem while saving file during action_update_new_user_fields. {$e->getMessage()}");
                }
            }
        }
        echo "DONE!!!<br>";
    }

    public function action_update_new_user_fields_mautic()
    {
        set_time_limit(0);
        $fileLoggerService = Container::get(FileLoggerService::class);

//        $offset = Input::get('offset') ?? 0;

        $path = APPPATH . '/tmp_offset_mautic_new_user_fields.txt';
        if (file_exists($path)) {
            $offset = intval(file_get_contents($path));
        } else {
            $offset = 0;
        }
        \Fuel\Core\Event::register('user_edit_profile', 'Events_User_Edit_Profile::handle');
        $users = Model_Whitelabel_User::find([
            "limit" => 100,
            "offset" => $offset,
            "order_by" => ["id" => "asc"]
        ]);
        if ($users) {
            if (empty($users)) {
                \Fuel\Core\Log::info("Mautic import completed...starting from 0.");
                file_put_contents($path, 0);
                echo "Mautic import completed...starting from 0.";
                return;
            }
            foreach ($users as $user) {
                $data = [
                    'first_deposit_amount_manager' => $user->first_deposit_amount_manager,
                    'total_deposit_manager' => $user->total_deposit_manager,
                    'total_withdrawal_manager' => $user->total_withdrawal_manager,
                    'total_purchases_manager' => $user->total_purchases_manager,
                    'total_net_income_manager' => $user->total_net_income_manager,
                    'last_deposit_date' => strtotime($user->last_deposit_date),
                    'last_purchase_date' => strtotime($user->last_purchase_date),
                    'last_deposit_amount_manager' => $user->last_deposit_amount_manager,
                    'last_purchase_amount_manager' => $user->last_purchase_amount_manager,
                    'net_winnings_manager' => $user->net_winnings_manager,
                    'sale_status' => $user->sale_status,
                    'pnl_manager' => $user->pnl_manager,
                ];

                \Fuel\Core\Event::trigger('user_edit_profile', [
                    'whitelabel_id' => $user->whitelabel_id,
                    'user_id' => $user->id,
                    'plugin_data' => $data,
                ]);
                echo "$user->email updated. <br>";
                try {
                    file_put_contents($path, $user->id);
                } catch (Exception $e) {
                    $fileLoggerService->error("Problem while saving file during action_update_new_user_fields. {$e->getMessage()}");
                }
            }
        }
        echo "DONE!!!<br>";
    }

    public function action_test_astropaycard()
    {
        $whitelabel = Model_Whitelabel::get_single_by_id(1);

        // At this moment it doesnt matter
        //$transaction = Model_Whitelabel_Transaction::find_by_pk(10);

        $payment_method_id = Helpers_Payment_Method::ASTRO_PAY_CARD; // It is equals to AstroPayCard

        $subtype = Model_Whitelabel_Payment_Method::find_by_payment_method_id($payment_method_id);

        $object = new Forms_Wordpress_Payment_Astropaycard();

        $object->set_whitelabel($whitelabel);
        //$object->set_transaction($transaction);
        $object->set_model_whitelabel_payment_method($subtype[0]);

        $x_card_num = "1616548016998793";
        $x_card_code = "2249";
        $x_exp_date = "06/2020";
        $x_amount = 1.01;
        $x_currency = "EUR";
        $x_unique_id = "LPU199976457";
        $x_invoice_num = "LPD123456789";

        $auth_transaction = $object->auth_transaction(
            $x_card_num,
            $x_card_code,
            $x_exp_date,
            $x_amount,
            $x_currency,
            $x_unique_id,
            $x_invoice_num
        );

        echo "<br><br>RESPONSE: <br>";

        var_dump($auth_transaction);

//        $curl_request = $object->get_make_curl_request();
//        var_dump($curl_request);
    }

    public function action_check_bitbaypay()
    {
        $whitelabel = Model_Whitelabel::get_single_by_id(1);

        $transaction = Model_Whitelabel_Transaction::find_by_pk(1);

        $payment_method_id = 15; // It is equals to BitBayPay

        $subtype = Model_Whitelabel_Payment_Method::find_by_payment_method_id($payment_method_id);
        var_dump($subtype[0]);
        $object = new Forms_Wordpress_Payment_Bitbaypay();

        $object->set_whitelabel($whitelabel);
        $object->set_transaction($transaction);
        $object->set_model_whitelabel_payment_method($subtype[0]);

        $curl_request = $object->get_make_curl_request();
        var_dump($curl_request);
    }

    public function action_show_tickets()
    {
        $whitelabel = Model_Whitelabel::get_single_by_id(1);

        $token = 424441357;

        $tickets = Model_Whitelabel_User_Ticket::get_single_with_currencies(
            $whitelabel,
            $token
        );

        var_dump($tickets);
        //die();

        if ($tickets === null && count($tickets) === 0) {
            return self::RESULT_INCORRECT_TICKET;
        }

        $ticket = $tickets[0];

        foreach ($tickets as $key => $ticket) {
            $ticket_lines = Model_Whitelabel_User_Ticket_Line::get_with_slip_by_ticket_id($ticket['id']);

            var_dump($ticket_lines);
        }
    }

    public function action_check_format_currency()
    {
        $number_value = 5.00;
        $currency_code = "EUR";
        $withfraction = true;
        $result = Lotto_View::format_currency($number_value, $currency_code, $withfraction);

        echo "First result: " . $result . "<br><br>";

        $number_value = 15.00;
        $currency_code = "";
        $withfraction = true;
        $result = Lotto_View::format_currency($number_value, $currency_code, $withfraction);
        echo "Second result: " . $result . "<br><br>";

        $number_value = 25.00;
        $currency_code = null;
        $withfraction = true;
        $result = Lotto_View::format_currency($number_value, $currency_code, $withfraction);
        echo "Second result: " . $result . "<br><br>";
    }

    public function action_check_deposit_count()
    {
        $add = "";
        $params = [];
        $whitelabel_type = 1;
        $whitelabel_id = 1;

        $deposits = Model_Whitelabel_Transaction::get_counted_deposits_for_reports(
            $add,
            $params,
            $whitelabel_type,
            $whitelabel_id
        );

        var_dump($deposits);
    }

    public function action_check_subtype()
    {
        $whitelabel = Model_Whitelabel::get_single_by_id(1);

        var_dump($whitelabel);

        $subtypes = Model_Whitelabel_Payment_Method::find([
            'where' => [
                'whitelabel_id' => (int)$whitelabel['id'],
                'payment_method_id' => Helpers_Payment_Method::VISANET,
            ],
            'limit' => 1
        ]);

        var_dump($subtypes);

        $transaction = Model_Whitelabel_Transaction::find_by_pk(184);
        //$transaction = Model_Whitelabel_Transaction::find_by_pk(176);

        var_dump($transaction);

//        $time_out_error = new stdClass();
//        $time_out_error->errorCode = -1;
//        $time_out_error->errorMessage = 'Info: user clicked log-out or time-out happened.';


        $time_out_error = [
            "errorMessage" => "Info: user clicked log-out or time-out happened."
        ];

        //$additional_data = json_encode($time_out_error, JSON_FORCE_OBJECT);

        //var_dump($additional_data);

        $set = [
            'additional_data' => serialize($time_out_error)
        ];

        var_dump($set);

        $transaction->set($set);
        $transaction->save();

        var_dump($transaction);
//
    }

    public function action_date_diff()
    {
        $user_id = 1;

        $user = Model_Whitelabel_User::find_by_pk($user_id);

        //var_dump($user);

        $current_date = new DateTime("now", new DateTimeZone("UTC"));
        //$register_date = $user['date_register'];
        $register_date = '2010-10-16';

        var_dump($current_date);

        var_dump($register_date);

        $register_date_time = new DateTime($register_date, new DateTimeZone("UTC"));

        $interval = $current_date->diff($register_date_time);

        var_dump((int)$interval->format('%y'));
    }

    public function action_purchase_numbers()
    {
        $whitelabel_id = (int)1;
        $user_id = (int)2;
        $type = Helpers_General::TYPE_TRANSACTION_PURCHASE;

        $number_of_customer_puchases = Model_Whitelabel_Transaction::get_count_for_user_by_type(
            $whitelabel_id,
            $user_id,
            $type
        );

        var_dump($number_of_customer_puchases);

        $last_purchase_date = Model_Whitelabel_Transaction::get_last_purchase_date($whitelabel_id, $user_id);

        var_dump($last_purchase_date);
    }

    public function action_check_tickets()
    {
        $tickets = Model_Whitelabel_User_Ticket::find_by_whitelabel_transaction_id(1);

        var_dump($tickets);
    }

    public function action_check_neteller()
    {
        // let's check for unfinished Neteller transactions from within 24h
        $transactions = Model_Whitelabel_Transaction::get_unfinished_or_with_error(
            null,
            Helpers_General::PAYMENT_TYPE_OTHER,
            Helpers_Payment_Method::NETELLER
        );

        var_dump($transactions);
    }

    public function action_validate_payment_method()
    {
        $currency_id = 4;
        $status = '';
        $payment_method_id = Helpers_Payment_Method::EASY_PAYMENT_GATEWAY;

        $list_of_payment_methods_classes = Helpers_Payment_Method::get_list_of_payment_method_classes_for_check_currency_support();

        if (array_key_exists($payment_method_id, $list_of_payment_methods_classes)) {
            $payment_method_class = new $list_of_payment_methods_classes[$payment_method_id]();
            $status = $payment_method_class->is_currency_supported(
                $payment_method_id,
                $currency_id
            );
        }

        var_dump($status);
    }

    public function action_create_users_from_csv()
    {
        if (!empty(Input::get('filename'))) {
            $filename = APPPATH . '/tmp/' . Input::get('filename');
            if (!file_exists($filename)) {
                echo "Error - file does not exist!";
                exit;
            }

            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');

            readfile($filename);
            unlink($filename);

            exit;
        }
        if (empty(Input::get('whitelabel'))) {
            echo "Whitelabel not specified.";
            exit;
        }
        $whitelabel_name = Input::get('whitelabel');

        if (!empty(Input::post('submit'))) {
            if (empty(Input::file("file"))) {
                echo "No file.";
                exit;
            }

            $file = Input::file("file");
            if ($file["error"] > 0) {
                echo "File error code: " . $file["error"] . "<br />";
                exit;
            }

            $headers = ['login', 'password', 'email'];
            $reader = new Services_File_Reader($file, $headers, Input::get('delimiter', ';'));
            $read_result = $reader->check_csv();
            if (!$read_result) {
                echo "File could not be read!";
                exit;
            }

            $whitelabel_array = Model_Whitelabel::get_by_username_with_default_currency($whitelabel_name);
            if (empty($whitelabel_array[0])) {
                echo "Wrong whitelabel!";
                exit;
            }

            $whitelabel = $whitelabel_array[0];

            $saver = new Services_User_Saver($read_result, $whitelabel);
            $save_result = $saver->save_data();

            echo $save_result;
        } else {
            echo '<!DOCTYPE html>' . "\n";
            echo '<html><head>' . "\n";
            echo '</head><body>' . "\n";
            echo '<form method="post" enctype="multipart/form-data" action="./?whitelabel=' . $whitelabel_name . '"><input type="file" name="file" id="file" /><input type="submit" name="submit"></form>';
            echo '</body></html>';
        }
    }

    public function action_update_balance_from_csv()
    {
        if (!empty(Input::get('filename'))) {
            $filename = APPPATH . '/tmp/' . Input::get('filename');
            if (!file_exists($filename)) {
                echo "Error - file does not exist!";
                exit;
            }

            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');

            readfile($filename);
            unlink($filename);

            exit;
        }
        if (empty(Input::get('whitelabel_name'))) {
            echo "Whitelabel not specified.";
            exit;
        }
        $whitelabel_name = Input::get('whitelabel_name');

        $delimiter = Input::get('delimiter');
        if (empty($delimiter)) {
            $delimiter = ';';
        }

        $is_bonus = Input::get('is_bonus');
        $is_bonus = $is_bonus === 'true' ? true : false;

        $use_emails = Input::get('use_emails');
        $use_emails = $use_emails === 'true' ? true : false;

        $filter_invalid = Input::get('filter_invalid');
        $filter_invalid = $filter_invalid === 'true' ? true : false;

        if (!empty(Input::post('submit'))) {
            if (empty(Input::file("file"))) {
                echo "No file.";
                exit;
            }

            $file = Input::file("file");
            if ($file["error"] > 0) {
                echo "File error code: " . $file["error"] . "<br />";
                exit;
            }

            $headers = ['no', 'user', 'amount', 'currency_code'];
            $reader = new Services_File_Reader($file, $headers, Input::get('delimiter', ';'));
            $read_result = $reader->check_csv();
            if (!$read_result) {
                echo "File could not be read!";
                exit;
            }

            $whitelabel = Model_Whitelabel::get_by_username($whitelabel_name);
            if (count($whitelabel) === 0) {
                echo "Wrong whitelabel.";
                exit;
            }

            if ($use_emails && $whitelabel['assert_unique_emails_for_users'] === "0") {
                echo "Option 'use_emails' cannot be used for whitelabel with non-unique e-mails!";
                exit;
            }

            $now = new DateTime("now", new DateTimeZone("UTC"));
            $start_date = $now->format("Y-m-d H:i:s");
            $saver = new Services_User_Balance_Saver($read_result, $whitelabel, $start_date, $filter_invalid, $is_bonus, $use_emails);
            $save_result = $saver->save_data();

            if (is_array($save_result)) {
                $limit = 100;
                $batched_array = array_chunk($save_result, $limit);
                $filename = $now->format("Y_m_d-H_i_s") . ".csv";
                $headers = ['no', 'user', 'amount', 'currency_code', 'errors'];

                $output = fopen(APPPATH . '/tmp/' . $filename, 'w');
                fputcsv($output, $headers, $delimiter);
                foreach ($batched_array as $batch) {
                    foreach ($batch as $data) {
                        fputcsv($output, $data, $delimiter);
                        ob_flush();
                        flush();
                    }
                }
                fclose($output);

                echo 'Data saved partially. Unsaved items are available <b><a href="./update_balance_from_csv?filename=' . $filename . '">here.</a></b>';
            } else {
                echo $save_result;
            }
        } else {
            echo '<!DOCTYPE html>' . "\n";
            echo '<html><head>' . "\n";
            echo '</head><body>' . "\n";
            echo '<form method="post" enctype="multipart/form-data" action="./update_balance_from_csv?whitelabel_name=' . $whitelabel_name . '&delimiter=' . $delimiter . '&filter_invalid=' . ($filter_invalid === true ? "true" : "false") . '&is_bonus=' . ($is_bonus === true ? "true" : "false") . '&use_emails=' . ($use_emails === true ? "true" : "false") . '"><input type="file" name="file" id="file" /><input type="submit" name="submit"></form>';
            echo '</body></html>';
        }
    }

    public function action_astro_banks_status()
    {
        $countries = [
            "AR" => "Argentina",
            "BR" => "Brazil",
            "CM" => "Cameroon",
            "CA" => "Canada",
            "CL" => "Chile",
            "CN" => "China",
            "CO" => "Colombia",
            "CI" => "Côte d'Ivoire",
            "DO" => "Dominican Republic",
            "EC" => "Ecuador",
            "GH" => "Ghana",
            "IN" => "India",
            "ID" => "Indonesia",
            "JP" => "Japan",
            "KE" => "Kenya",
            "MY" => "Malaysia",
            "MX" => "Mexico",
            "NG" => "Nigeria",
            "PA" => "Panama",
            "PE" => "Peru",
            "PY" => "Paraguay",
            "PH" => "Philippines",
            "ZA" => "South Africa",
            "TZ" => "Tanzania",
            "TH" => "Thailand",
            "UG" => "Uganda",
            "UY" => "Uruguay",
            "VN" => "Vietnam"
        ];

        $banks = '';
        $active = 'Active Astro Banks: <br>';
        $inactive = 'Inactive Astro Banks: <br>';
        $errors = 'Errors: <br>';

        foreach ($countries as $countryCode => $countryName) {
            $cacheKey = 'astro_pay_banks' . $countryCode;

            try {
                $banks = Cache::get($cacheKey);
            } catch (CacheNotFoundException $exception) {
                $banks = $this->retrieveBanks($countryCode);
                Cache::set($cacheKey, $banks, Helpers_Time::MINUTE_IN_SECONDS);
            }

            if (!empty($banks) && !isset($banks['error'])) {
                $active .= '<b>' . $countryName . '</b><br>';
                foreach ($banks as $bank) {
                    $active .= '- ' . $bank . '<br>';
                }
            } else {
                $inactive .= $countryName . ', ';
                $errors .= $banks['error'] . ' ' . $countryName . '<br>';
            }
        }
        $banks = $active . "<br>" . $inactive . "<br>" . $errors;
        return $banks;
    }

    /**
     * this function is copied from
     * /platform/fuel/app/classes/presenter/wordpress/base/box/payment/other.* php
     * to keep it private and change $astroPayMethod variable.
     */
    private function retrieveBanks(string $countryCode): array
    {
        $astroPayMethod = null;
        $data = null;
        $response = null;
        $decodedResponse = null;

        try {
            $astroPayMethod = WhitelabelPaymentMethod::query()
                ->where('whitelabel_id', 1)
                ->where('payment_method_id', 19)
                ->get_one();
            $data = unserialize($astroPayMethod['data']);

            $streamline = new Helpers_Payment_Astropay_Streamline(
                $data['login'],
                $data['password'],
                $data['secret_key'],
                $data['is_test']
            );
            $response = $streamline->get_banks_by_country($countryCode);

            if ($response === false) {
                throw new Exception("curl returned false.");
            }
            $decodedResponse = json_decode($response, true);

            $isResponseValid = !isset($decodedResponse[0]) || !isset($decodedResponse[0]['code']) ? false : true;

            if ($isResponseValid === false) {
                throw new Exception("invalid response");
            }
            $banks = [];
            foreach ($decodedResponse as $bank) {
                $banks[$bank['code']] = $bank['name'];
            }
            return $banks;
        } catch (Throwable $throwable) {
            $err = $throwable->getMessage();
            return ['error' => $err];
        }
    }
}
