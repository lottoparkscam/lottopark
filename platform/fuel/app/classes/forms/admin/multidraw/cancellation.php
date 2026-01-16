<?php

/**
 *
 */
class Forms_Admin_Multidraw_Cancellation
{
    /**
     * Get Trait for date range preparation
     */
    use Traits_Gets_Date;

    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     * @var
     */
    private $lottery_id;

    /**
     * @var
     */
    private $user;

    /**
     *
     * @var int
     */
    private $range_from;

    /**
     *
     * @var int
     */
    private $source;

    /**
     *
     * @var int
     */
    private $multidraws_helper;

    /**
     *
     * @var int
     */
    private $items_per_page = 25;

    /**
     *
     * @var View
     */
    private $inside = null;

    /**
     * Forms_Admin_Multidraw_Cancellation constructor.
     * @param $lottery_id
     * @param $range_from
     */
    public function __construct($lottery_id, $range_from)
    {
        $this->lottery_id = $lottery_id;
        $this->range_from = $range_from;
    }

    /**
     *
     * @return int
     */
    public function get_source(): int
    {
        return $this->source;
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
     * @param string $view_template
     * @return int
     */
    public function process_form($view_template)
    {
        // Get multitdraws
        $multidraw_helper = new Helpers_Multidraw([]);
        $multidraws = $multidraw_helper->get_multidraws_for_cancellation($this->lottery_id, $this->range_from);
        $lottery = Model_Lottery::find_by_pk($this->lottery_id);

        if (empty($multidraws)) {
            Session::set_flash("message", ["danger", _("Multidraw doesn't exist")]);
            Response::redirect('multidraw_tickets');
        }

        $details = [
            'lottery_name' => $lottery['name'],
            'range_from' => $this->range_from,
            'lottery_id' => $this->lottery_id
        ];

        $whitelabel = $this->get_whitelabel();

        $this->inside = View::forge($view_template);

        $this->inside->set('cancellation', $multidraws);
        $this->inside->set('details', $details);

        return true;
    }

    /**
     * @param $ticket_cancellation
     * @throws Exception
     */
    public function confirm_cancellation()
    {
        $lottery = Model_Lottery::find_by_pk($this->lottery_id);

        // Get multitdraws
        $multidraw_helper = new Helpers_Multidraw([]);
        $multidraw_helper->confirm_massive_multidraw_cancellation($lottery['name'], $this->lottery_id, $this->range_from);

        Session::set_flash("message", ["success", _("Multidraw has been successfully cancelled! Lottery: ".$lottery['name'].", Range from: ".$this->range_from."")]);
        Response::redirect('multidraw_tickets');
    }
}
