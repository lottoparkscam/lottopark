<?php
use Repositories\WhitelabelAffSlotCommissionRepository;
use Services\AffCasinoCommissionService;

/**
 * @deprecated
 * Description of Forms_Whitelabel_Aff_Reports
 */
class Forms_Whitelabel_Aff_Reports
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
     * @var array
     */
    private $eaffs = [];
    
    /**
     *
     * @var array
     */
    private $countries = [];

    private WhitelabelAffSlotCommissionRepository $whitelabelAffSlotCommissionRepository;

    private $pki;
    
    /**
     *
     * @param array $whitelabel
     * @param array $countries
     */
    public function __construct(array $whitelabel, array $countries)
    {
        $this->whitelabel = $whitelabel;
        $this->whitelabelAffSlotCommissionRepository = Container::get(WhitelabelAffSlotCommissionRepository::class);
        $this->affCasinoCommissionService = Container::get(AffCasinoCommissionService::class);
        $this->pki = new Helpers_Whitelabel($whitelabel);
        $this->countries = $countries;
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
        $whitelabel = $this->get_whitelabel();
        
        $inside = Presenter::forge("whitelabel/affs/reports/index");

        if (Input::get("filter.range_start") === null) {
            $this->inside = $inside;
            return ;
        }
        
        $add_filters = [];
        $add_filters_clicks = [];
        $add_filters_commission = [];
        $params = [];

        if (Input::get('filter.email') != null) {
            $email = Input::get('filter.email');
            $this->eaffs = $this->pki->prepare_eaffs($email);

            $add_filters[] = " AND whitelabel_user_aff.whitelabel_aff_id = :aff";
            $add_filters_clicks[] = " AND whitelabel_aff.id = :aff";
            $add_filters_commission[] = " AND whitelabel_aff_commission.whitelabel_aff_id = :aff";
            $params[] = [":aff", $this->eaffs[$email]['id']];
        }

        $currencies = Lotto_Settings::getInstance()->get("currencies");

        $inside->set("countries", $this->countries);
        $inside->set("currencies", $currencies);

        $date_start = DateTime::createFromFormat(
            "m/d/Y",
            Input::get("filter.range_start"),
            new DateTimeZone("UTC")
        );
        $date_end = DateTime::createFromFormat(
            "m/d/Y",
            Input::get("filter.range_end"),
            new DateTimeZone("UTC")
        );
        if ($date_end === false) {
            $date_end = new DateTime("now", new DateTimeZone("UTC"));
        }
        $date_start->setTime(0, 0, 0);
        $date_end->setTime(23, 59, 59);

        // add dates to global params
        $params[] = [":date_start", $date_start->format("Y-m-d H:i:s")];
        $params[] = [":date_end", $date_end->format("Y-m-d H:i:s")];
        
        $inside->set("date_start", $date_start->format("Y-m-d H:i:s"));
        $inside->set("date_end", $date_end->format("Y-m-d H:i:s"));
        
        // TODO: move results to presenter to prepare data
        $clicks = Model_Whitelabel_Aff_Click::fetch_clicks_for_whitelabel(
            $add_filters_clicks,
            $params,
            $whitelabel['id']
        );
        
        $inside->set("clicks", $clicks);

        // TODO: move results to presenter to prepare data
        $regcount = Model_Whitelabel_User_Aff::fetch_regcount_for_reports_for_whitelabel(
            $add_filters,
            $params,
            $whitelabel['id']
        );
        
        $inside->set("regcount", $regcount);

        // TODO: move results to presenter to prepare data
        $ftpcount = Model_Whitelabel_User_Aff::fetch_ftp_for_reports_for_whitelabel(
            $add_filters,
            $params,
            $whitelabel['id']
        );
        
        $inside->set("ftpcount", $ftpcount);

        $ftdCount = Model_Whitelabel_User_Aff::fetch_ftd_for_reports_for_whitelabel(
            $add_filters,
            $params,
            $whitelabel['id']
        );

        $inside->set('ftdCount', $ftdCount);

        $commissions = Model_Whitelabel_Aff_Commission::fetch_commissions_for_whitelabel_report(
            $add_filters_commission,
            $params,
            $whitelabel['id'],
            $date_start->format("Y-m-d H:i:s"),
            $date_end->format("Y-m-d H:i:s")
        );

        $findAffByToken = true;
        $filters = $this->affCasinoCommissionService->prepareCasinoCommissionFilters($findAffByToken);
        $casinoCommissions = $this->whitelabelAffSlotCommissionRepository->findCasinoCommissionsByReport(
            $filters,
            null,
            $whitelabel['id'],
            $date_start->format('Y-m-d H:i:s'),
            $date_end->format('Y-m-d H:i:s')
        );

        $totalLotteryCommissionManager = 0;
        $totalCasinoCommissionManager = 0;
        foreach ($commissions as $commission) {
            $totalLotteryCommissionManager = round($totalLotteryCommissionManager + $commission['commission_manager'], 2);
        }

        foreach ($casinoCommissions as $commission) {
            $totalCasinoCommissionManager = round($totalCasinoCommissionManager + $commission['daily_commission_usd'], 2);
        }

        $totalCommissionManager = round($totalLotteryCommissionManager + $totalCasinoCommissionManager, 2);

        $inside->set('totalLotteryCommission', $totalLotteryCommissionManager);
        $inside->set('totalCasinoCommission', $totalCasinoCommissionManager);
        $inside->set('totalCommission', $totalCommissionManager);
        $inside->set('commissions', $commissions);
        $inside->set('casinoCommissions', $casinoCommissions);
        
        $this->inside = $inside;
    }
}
