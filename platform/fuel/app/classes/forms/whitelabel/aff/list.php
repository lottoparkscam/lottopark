<?php

use Fuel\Core\View;
use Helpers\AffGroupHelper;
use Repositories\Aff\WhitelabelAffRepository;
use Repositories\WhitelabelAffCasinoGroupRepository;

/**
 * @deprecated
 * Description of Forms_Whitelabel_Aff_List
 */
class Forms_Whitelabel_Aff_List
{
    private array $whitelabel;
    private int $items_per_page = 25;
    private View $inside;
    private int $deleted = 0;
    private int $accepted = 1;
    private string $link = "/affs/list";
    private string $rparam;
    private array $countries;
    private array $languages;

    private WhitelabelAffCasinoGroupRepository $whitelabelAffCasinoGroupRepository;

    public function __construct($whitelabel, $rparam, $countries, $languages)
    {
        $this->whitelabel = $whitelabel;
        $this->rparam = $rparam;
        $this->countries = $countries;
        $this->languages = $languages;
        $this->whitelabelAffCasinoGroupRepository = Container::get(WhitelabelAffCasinoGroupRepository::class);

        if ($this->rparam == "deleted") {
            $this->deleted = 1;
            $this->link = "/affs/deleted";
        } elseif ($this->rparam == "notaccepted") {
            $this->accepted = 0;
            $this->link = "/affs/notaccepted";
        } elseif ($this->rparam == "inactive") {
            $this->link = "/affs/inactive";
        }
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
     * @return array
     */
    private function prepare_filters(): array
    {
        $filter_add = [];
        $params = [];

        if (Input::get("filter.id") != null) {
            $filter_add[] = " AND token = :token";
            $params[] = [":token", Input::get("filter.id")];
        }
        
        if (Input::get("filter.email") != null) {
            $filter_add[] = " AND email LIKE :email";
            $params[] = [":email", '%' . Input::get("filter.email") . '%'];
        }
        
        if (Input::get("filter.login") != null) {
            $filter_add[] = " AND login LIKE :login";
            $params[] = [":login", '%' . Input::get("filter.login") . '%'];
        }
        
        if (Input::get("filter.language") != null &&
            Input::get("filter.language") != "a"
        ) {
            $filter_add[] = " AND language_id = :language";
            $params[] = [":language", intval(Input::get("filter.language"))];
        }
        
        if (Input::get("filter.country") != null &&
            Input::get("filter.country") != "a"
        ) {
            $filter_add[] = " AND country = :country";
            $params[] = [":country", Input::get("filter.country")];
        }

        $isAffUser = Input::get('filter.isAffUser');
        if ($isAffUser === '1') {
            $filter_add[] = " AND is_aff_user = :isAffUser";
            $params[] = [':isAffUser', true];
        } 
        if ($isAffUser === '0') {
            $filter_add[] = " AND is_aff_user = :isAffUser";
            $params[] = [':isAffUser', false];
        }

        $lotteryGroup = Input::get('filter.lotteryGroup');
        $isNotSelectedAllGroups = $lotteryGroup != 'a';
        if (!empty($lotteryGroup) && $isNotSelectedAllGroups) {
            if ($lotteryGroup == 'default') {
                $filter_add[] = ' AND whitelabel_aff_group_id IS NULL';
            } else {
                $filter_add[] = ' AND whitelabel_aff_group_id = :group';
                $params[] = [':group', (int)$lotteryGroup];
            }
        }

        $casinoGroup = Input::get('filter.casinoGroup');
        $isNotSelectedAllGroups = $casinoGroup != 'a';
        if (!empty($casinoGroup) && $isNotSelectedAllGroups) {
            if ($casinoGroup == 'default') {
                $filter_add[] = ' AND whitelabel_aff_casino_group_id IS NULL';
            } else {
                $filter_add[] = ' AND whitelabel_aff_casino_group_id = :group';
                $params[] = [':group', (int)$casinoGroup];
            }
        }
        
        if (Input::get("filter.name") != null) {
            $filter_add[] = " AND name LIKE :name";
            $params[] = [":name", '%' . Input::get("filter.name") . '%'];
        }
        if (Input::get("filter.surname") != null) {
            $filter_add[] = " AND surname LIKE :surname";
            $params[] = [":surname", '%' . Input::get("filter.surname") . '%'];
        }
        
        $filter_add_whole = implode("", $filter_add);

        return [$filter_add_whole, $params];
    }
    
    /**
     *
     * @return array
     */
    private function add_additional_filters(): array
    {
        $addarr = [];
        
        $is_active = 1;
        if ($this->rparam == "inactive") {
            $is_active = 0;
        }
        $addarr[] = "is_active = " . $is_active;
        
        if ((int)$this->whitelabel['aff_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED) {
//            $addarr[] = "is_confirmed = " . ($rparam == "inactive" ? "0" : "1");
            $addarr[] = "is_confirmed = " . $is_active;
        }
        
        $add = "";
        $accepted_add = " AND is_accepted = " . $this->accepted;
        
        if ($this->rparam == null || $this->rparam == "notaccepted") {
            $add = ' AND (' . implode(" AND ", $addarr) . ')';
        } elseif ($this->rparam == "inactive") {
            $accepted_add = "";
            $add = ' AND (' . implode(" OR ", $addarr) . ')';
        }
        
        return [
            $accepted_add,
            $add
        ];
    }
    
    /**
     *
     * @param string $accepted_add
     * @param string $add
     * @param string $filter_add
     * @param array $sort
     * @param array $params
     * @param array $rallaffs
     * @param array $groups
     * @return void
     */
    private function export_as_csv(
        $accepted_add,
        $add,
        $filter_add,
        $sort,
        $params,
        $rallaffs,
        $groups
    ): void {
        $currencies = Helpers_Currency::getCurrencies();
        $whitelabel = $this->get_whitelabel();
            
        $users = Model_Whitelabel_Aff::fetch_data_filtered_by_whitelabel(
            $params,
            $accepted_add,
            $add,
            $filter_add,
            $whitelabel['id'],
            $this->deleted,
            $sort['db'],
            false
        );

        $dt = new DateTime("now", new DateTimeZone("UTC"));
        $filename = $this->rparam . "_" . $dt->format("Y_m_d-H_i_s") . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        
        $headers = [
            _("Affiliate ID"),
            _("Parent Affiliate"),
            _("Login"),
            _("First Name"),
            _("Last Name"),
            _("E-mail"),
            _("Group"),
            _("Currency"),
            _("Phone Country"),
            _("Phone"),
            _("Country"),
            _("Address #1"),
            _("Address #2"),
            _("City"),
            _("Region"),
            _("Postal/ZIP Code"),
            _("Birthdate"),
            _("Date created"),
            _("Last Active"),
            _("Language"),
            _("Time Zone")
        ];
        
        fputcsv($output, $headers);
        if ($users !== null && count($users) > 0) {
            foreach ($users as $user) {
                $data = [
                    strtoupper($user['token']),
                    !empty($user['whitelabel_aff_parent_id']) ? strtoupper($rallaffs[$user['whitelabel_aff_parent_id']]['token']) : null,
                    $user['login'],
                    $user['name'],
                    $user['surname'],
                    $user['email'],
                    !empty($user['whitelabel_aff_group_id']) ? $groups[$user['whitelabel_aff_group_id']]['name'] : _("Default"),
                    $currencies[$user['currency_id']]['code'],
                    $user['phone_country'],
                    $user['phone'],
                    $user['country'],
                    $user['address_1'],
                    $user['address_2'],
                    $user['city'],
                    $user['state'],
                    $user['zip'],
                    !empty($user['birthdate']) ? $user['birthdate'] : '',
                    $user['date_created'],
                    $user['last_active'],
                    str_replace('_', '-', $this->languages[$user['language_id']]['code']),
                    $user['timezone']
                ];
                fputcsv($output, $data);
            }
        }
        fclose($output);
        exit();
    }

    private function prepareCasinoGroups(array $casinoGroups): Generator
    {
        foreach ($casinoGroups as $casinoGroup) {
            yield $casinoGroup['id'] => $casinoGroup;
        }
    }

    public function process_form(): void
    {
        $whitelabel = $this->get_whitelabel();

        $inside = View::forge("whitelabel/affs/index");

        list(
            $filter_add,
            $params
        ) = $this->prepare_filters();

        list(
            $accepted_add,
            $add
        ) = $this->add_additional_filters();

        $sort_arr = [
            'name' => 'asc',
            'id' => 'asc',
            'last_active' => 'desc'
        ];
        $defsort = ['id', 'asc'];

        if ($this->deleted == 1) {
            unset($sort_arr['last_active']);
            $accepted_add = "";
            $sort_arr['date_delete'] = 'desc';
            $defsort = ["date_delete", 'asc'];
        }

        $sort = Lotto_Helper::get_sort($sort_arr, $defsort, $this->link);

        $xsort = explode(' ', $sort['db']);
        if ($xsort[0] == "name") {
            $sort['db'] = "name " . $xsort[1] . ", surname " . $xsort[1];
        }

        $lotteryGroups = Model_Whitelabel_Aff_Group::get_whitelabel_groups($whitelabel);
        $prepareCasinoGroups = AffGroupHelper::prepareCasinoGroups(
            $this->whitelabelAffCasinoGroupRepository->getGroupsByWhitelabelId($whitelabel['id'])
        );

        $casinoGroups = iterator_to_array($prepareCasinoGroups);

        $count = Model_Whitelabel_Aff::fetch_count_filtered_by_whitelabel(
            $params,
            $accepted_add,
            $add,
            $filter_add,
            $whitelabel['id'],
            $this->deleted
        );

        $config = [
            'pagination_url' => $this->link . '?' . http_build_query(Input::get()),
            'total_items' => $count,
            'per_page' => $this->items_per_page,
            'uri_segment' => 'page'
        ];
        $pagination = Pagination::forge('affspagination', $config);

        $affs = Model_Whitelabel_Aff::fetch_data_filtered_by_whitelabel(
            $params,
            $accepted_add,
            $add,
            $filter_add,
            $whitelabel['id'],
            $this->deleted,
            $sort['db'],
            true,
            $pagination->offset,
            $pagination->per_page
        );

        $affiliateIds = array_column($affs, 'whitelabel_aff_parent_id');
        $whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
        $affiliatesDetails = $whitelabelAffRepository->getAffiliatesDetailsByIds($affiliateIds, $whitelabel['id']);

        $affiliatesDetailsFormatted = [];
        foreach ($affiliatesDetails as $affiliate) {
            $affiliatesDetailsFormatted[$affiliate['id']] = $affiliate;
        }

        $inside->set("rallaffs", $affiliatesDetailsFormatted);

        $currencies = Lotto_Settings::getInstance()->get("currencies");

        $inside->set("pages", $pagination);
        $inside->set("sort", $sort);
        $inside->set("affs", $affs);
        $inside->set("countries", $this->countries);
        $inside->set("currencies", $currencies);
        $inside->set("languages", $this->languages);
        $inside->set('lotteryGroups', $lotteryGroups);
        $inside->set('casinoGroups', $casinoGroups);

        $this->inside = $inside;
    }

    /**
     *
     * @return void
     */
    public function process_form_export(): void
    {
        $whitelabel = $this->get_whitelabel();
        
        list(
            $filter_add,
            $params
        ) = $this->prepare_filters();
        
        list(
            $accepted_add,
            $add
        ) = $this->add_additional_filters();
        
        $sort_arr = [
            'name' => 'asc',
            'id' => 'asc',
            'last_active' => 'desc'
        ];
        $defsort = ['id', 'asc'];
        
        if ($this->deleted == 1) {
            unset($sort_arr['last_active']);
            $accepted_add = "";
            $sort_arr['date_delete'] = 'desc';
            $defsort = ["date_delete", 'asc'];
        }

        $sort = Lotto_Helper::get_sort($sort_arr, $defsort, $this->link);
        
        $xsort = explode(' ', $sort['db']);
        if ($xsort[0] == "name") {
            $sort['db'] = "name " . $xsort[1] . ", surname " . $xsort[1];
        }
        
        $all_affs = Model_Whitelabel_Aff::get_all_for_whitelabel($whitelabel);
        
        $real_all_affs = [];
        foreach ($all_affs as $item) {
            $real_all_affs[$item['id']] = $item;
        }
        
        $groups = Model_Whitelabel_Aff_Group::get_whitelabel_groups($whitelabel);
                
        $this->export_as_csv(
            $accepted_add,
            $add,
            $filter_add,
            $sort,
            $params,
            $real_all_affs,
            $groups
        );
    }
}
