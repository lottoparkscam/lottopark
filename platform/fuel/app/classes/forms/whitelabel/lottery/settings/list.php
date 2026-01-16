<?php

/**
 * Description of Forms_Whitelabel_Lottery_Settings_List
 */
class Forms_Whitelabel_Lottery_Settings_List
{
    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var View
     */
    private $inside = null;
    
    /**
     *
     * @param array $whitelabel
     */
    public function __construct(array $whitelabel)
    {
        $this->whitelabel = $whitelabel;
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel()
    {
        return $this->whitelabel;
    }

    /**
     *
     * @return View
     */
    public function get_inside()
    {
        return $this->inside;
    }
    
    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        $inside = Presenter::forge("whitelabel/lottery/settings/list");

        $lotteries = Model_Lottery::get_all_lotteries_for_whitelabel($this->whitelabel);
        // we want to temporarily disable the display and editing of GG World Keno in the manager
        // the business team decided to do so
        $lotteries = array_filter(
            $lotteries,
            fn($lottery) => isset($lottery['type']) && Helpers_Lottery::isGgrNotEnabled($lottery['type'])
        );

        $inside->set("lotteries", $lotteries);

        $this->inside = $inside;
    }
}
