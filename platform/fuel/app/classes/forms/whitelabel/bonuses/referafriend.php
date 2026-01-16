<?php

use Fuel\Core\Input;
use Fuel\Core\Session;
use Fuel\Core\Validation;

/**
 * Description of Forms_Whitelabel_Bonuses_Referafriend
 */
class Forms_Whitelabel_Bonuses_Referafriend extends Forms_Whitelabel_Bonuses_Main
{
    /**
     *
     * @var type
     */
    private $inside = null;

    /**
     *
     * @param array $whitelabel
     */
    public function __construct(array $whitelabel)
    {
        $this->whitelabel = $whitelabel;
        $path_to_view = 'whitelabel/bonuses/referafriend';
        $this->inside = Presenter::forge($path_to_view);
    }

    /**
     *
     * @return type
     */
    public function get_inside()
    {
        return $this->inside;
    }

    /**
     *
     * @return int
     */
    public function get_max_lottery_id(): int
    {
        $max_lottery_id = 0;

        $last_key_id = 0;
        if (!empty($this->lotteries)) {
            $last_key_id = count($this->lotteries) - 1;
            $max_lottery_id = (int) $this->lotteries[$last_key_id]['id'];
        }

        return $max_lottery_id;
    }

    /**
     *
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();

        $validation->add('lottery', _('Lottery'))
                ->add_rule('trim')
                ->add_rule('required')
                ->add_rule('is_numeric')
                ->add_rule('numeric_min', 0)
                ->add_rule('numeric_max', $this->get_max_lottery_id());

        $validation->add('min_total_purchase', _('Minimum total purchase'))
                ->add_rule('trim')
                ->add_rule('required')
                ->add_rule('is_numeric')
                ->add_rule("numeric_min", 0);

        return $validation;
    }

    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        $this->lotteries = Model_Lottery::get_all_lotteries_for_whitelabel_short(
            $this->whitelabel['id']
        );

        $this->inside->set('lotteries', $this->lotteries);

        $bonus = Model_Whitelabel_Bonus::find_one_by(
            [
                'whitelabel_id' => $this->whitelabel['id'],
                'bonus_id' => Forms_Whitelabel_Bonuses_Main::BONUS_REFER_A_FRIEND
            ]
        );

        if (Input::post()) {
            $validation = $this->validate_form();
            if ($validation->run()) {
                $errors = Lotto_Helper::generate_errors($validation->error());
                $this->inside->set("errors", $errors);

                if (Input::post('lottery') == 0) {
                    if (!empty($bonus)) {
                        $bonus->delete();
                        $bonus = null;
                    }
                } else {
                    if (empty($bonus)) {
                        $bonus = Model_Whitelabel_Bonus::forge();
                    }

                    $bonus->set([
                        'whitelabel_id' => $this->whitelabel['id'],
                        'bonus_id' => Forms_Whitelabel_Bonuses_Main::BONUS_REFER_A_FRIEND,
                        'purchase_lottery_id' => Input::post('lottery'),
                        'min_total_purchase' => Input::post('min_total_purchase')
                    ]);
                    $bonus->save();
                    Session::set_flash('message', ['success', _('Refer a friend bonus successfully set!')]);
                }
            } else {
                $errors = Lotto_Helper::generate_errors($validation->error());
                $this->inside->set("errors", $errors);
            }
        }

        $this->inside->set('lottery_id', empty($bonus->purchase_lottery_id) ? 0 : $bonus->purchase_lottery_id);
        $this->inside->set('min_total_purchase', empty($bonus->min_total_purchase) ? '0.00' : $bonus->min_total_purchase);
    }
}
