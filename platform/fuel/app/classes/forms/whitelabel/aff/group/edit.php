<?php

use Fuel\Core\Validation;
use Fuel\Core\Input;
use Fuel\Core\Security;
use Fuel\Core\Session;
use Fuel\Core\View;

/**
 * @deprecated
 * Description of Forms_Whitelabel_Aff_Group_Edit
 */
class Forms_Whitelabel_Aff_Group_Edit extends Forms_Main
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
     * @var bool
     */
    private $edit_default = false;

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
    public function get_whitelabel(): array
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
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $val = Validation::forge();
        $maxPercentage = 100;

        if (!$this->edit_default) {
            $val->add("input.name", _('Name'))
                ->add_rule('trim')
                ->add_rule("required")
                ->add_rule('min_length', 3)
                ->add_rule('max_length', 40)
                ->add_rule('valid_string', ['alpha', 'numeric', 'specials', 'dashes', 'spaces', 'utf8']);
        }

        if (!empty(Input::post("input.ftpcommissionvalue"))) {
            $val->add("input.ftpcommissionvalue", _("1st-tier First Time Purchase commission value"))
                ->add_rule("trim")
                ->add_rule("is_numeric")
                ->add_rule("numeric_min", 0)
                ->add_rule("numeric_max", $maxPercentage);
        }

        if (!empty(Input::post("input.ftpcommissionvalue"))) {
            $val->add("input.ftpcommissionvalue2", _("2nd-tier First Time Purchase commission value"))
                ->add_rule("trim")
                ->add_rule("is_numeric")
                ->add_rule("numeric_min", 0)
                ->add_rule("numeric_max", $maxPercentage);
        }

        return $val;
    }

    private function validate_second_form(): Validation
    {
        $val_sec = Validation::forge("type");
        $maxPercentage = 100;

        if (!empty(Input::post("input.commissionvalue"))) {
            $val_sec->add("input.commissionvalue", _("1st-tier sale commission value"))
                ->add_rule("trim")
                ->add_rule("is_numeric")
                ->add_rule("numeric_min", 0)
                ->add_rule("numeric_max", $maxPercentage);
        }

        if (!empty(Input::post("input.commissionvalue2"))) {
            $val_sec->add("input.commissionvalue2", _("2nd-tier sale commission value"))
                ->add_rule("trim")
                ->add_rule("is_numeric")
                ->add_rule("numeric_min", 0)
                ->add_rule("numeric_max", $maxPercentage);
        }

        return $val_sec;
    }

    /**
     *
     * @param mixed $edit
     * @param mixed $edit_lp
     * @return array
     */
    private function preper_group_data($edit, $edit_lp): array
    {
        $whitelabel = $this->get_whitelabel();

        $manager_currency_tab = Helpers_Currency::get_mtab_currency(
            true,
            "",
            $whitelabel['manager_site_currency_id']
        );

        $group_data = [];

        $title = _("New affiliate group");
        if (isset($edit)) {
            $title_add_t = '';
            if ($this->edit_default) {
                $title_add_t = _("Default Group");
            } else {
                $title_add_t = $edit['name'];
            }
            $title_add = Security::htmlentities($title_add_t);

            $title = _("Edit affiliate lottery group");
            $title .= " <small>";
            $title .= $title_add;
            $title .= "</small>";
        }
        $group_data['title'] = $title;

        $start_url = "/affs/lottery-groups";

        $group_data['back_url'] = $start_url;

        $edit_url_rest = '/new';
        if (isset($edit_lp)) {
            $edit_url_rest = '/edit/' . $edit_lp;
        }
        $group_data['action_url'] = $start_url . $edit_url_rest;

        $fieldParams = [
            ['name', 'name', 'name'],
            ['commissionvalue', 'commission_value_manager', 'commission_value'],
            ['commissionvalue2', 'commission_value_2_manager', 'commission_value_2'],
            ['ftpcommissionvalue', 'ftp_commission_value_manager', 'ftp_commission_value'],
            ['ftpcommissionvalue2', 'ftp_commission_value_2_manager', 'ftp_commission_value_2'],
        ];

        foreach ($fieldParams as $field) {
            $value = '';
            $inputValue = Input::post('input.' . $field[0]);
            if (!empty($inputValue)) {
                $value = $inputValue;
            }
            elseif (isset($edit[$field[1]])) {
                $value = $edit[$field[1]];
            }

            $group_data[$field[2]] = $value;
        }

        $group_data['currency_code'] = Lotto_View::format_currency_code($manager_currency_tab['code']);
        return $group_data;
    }

    /**
     *
     * @param string $action_name
     * @param string $token
     * @return int
     */
    public function process_form(string $action_name, string $token = ""): int
    {
        $whitelabel = $this->get_whitelabel();

        $this->inside = View::forge("whitelabel/affs/groups_edit");

        $edit = null;
        $edit_lp = null;

        // Flag to check if it is attached with default
        // group (some values within whitelabel table)
        $this->edit_default = false;

        if ($action_name == "edit") {
            $id = $token == "default" ? "default" : intval($token);
            $edit_lp = $id;

            if ((int)$id >= 1) { // Different than default (within Aff Group)
                $edit_obj = Model_Whitelabel_Aff_Group::find([
                    "where" => ["whitelabel_id" => $whitelabel['id']],
                    "order_by" => ["name" => "ASC"],
                    "limit" => 1,
                    "offset" => $id - 1
                ]);

                if (!empty($edit_obj) && count($edit_obj) == 1) {
                    $edit = $edit_obj[0];
                } else {
                    Session::set_flash("message", ["danger", _("Wrong affiliate group!")]);

                    return self::RESULT_WRONG_AFF_GROUP;
                }
            } elseif ($id === "default") {
                $this->edit_default = true;
                $edit = [
                    "commission_value_manager" => $whitelabel['def_commission_value_manager'],
                    "commission_value_2_manager" => $whitelabel['def_commission_value_2_manager'],
                    "ftp_commission_value_manager" => $whitelabel['def_ftp_commission_value_manager'],
                    "ftp_commission_value_2_manager" => $whitelabel['def_ftp_commission_value_2_manager'],
                ];
            } else {
                Session::set_flash("message", ["danger", _("Wrong affiliate group!")]);

                return self::RESULT_WRONG_AFF_GROUP;
            }
        }

        $group_data = $this->preper_group_data($edit, $edit_lp);

        $this->inside->set("group_data", $group_data);
        $this->inside->set("edit_def", $this->edit_default);

        $val = $this->validate_form();

        if ($val->run()) {
            $val2 = $this->validate_second_form();

            if ($val2->run()) {
                $res = 0;
                if (!$this->edit_default) { // Not for default
                    $cnt_set = [
                        "whitelabel_id" => $whitelabel['id'],
                        "name" => $val->validated("input.name")
                    ];
                    if (!empty($edit)) {
                        $cnt_set[] = ["id", "!=", $edit['id']];
                    }

                    $res = Model_Whitelabel_Aff_Group::count(null, true, $cnt_set);
                }

                if ($res > 0) {
                    $msg = _("The affiliate group with this name already exists!");
                    $errors = ['input.name' => $msg];
                    $this->inside->set("errors", $errors);

                    return self::RESULT_WITH_ERRORS;
                }

                $group = null;
                if (empty($edit)) {         // New group
                    $group = Model_Whitelabel_Aff_Group::forge();
                } elseif (!$this->edit_default) {     // Not default group
                    $group = $edit;
                }

                // This is not edit for default
                // So, this is new one or edit for existed within Aff Group table
                if (!$this->edit_default) {
                    $commission_value_manager = null;
                    if (!empty($val2->validated("input.commissionvalue"))) {
                        $commission_value_manager = $val2->validated("input.commissionvalue");
                    }

                    $commission_value_2_manager = null;
                    if (!empty($val2->validated("input.commissionvalue2"))) {
                        $commission_value_2_manager = $val2->validated("input.commissionvalue2");
                    }

                    $ftp_commission_value_manager = null;
                    if (!empty($val->validated("input.ftpcommissionvalue"))) {
                        $ftp_commission_value_manager = $val->validated("input.ftpcommissionvalue");
                    }

                    $ftp_commission_value_2_manager = null;
                    if (!empty($val->validated("input.ftpcommissionvalue2"))) {
                        $ftp_commission_value_2_manager = $val->validated("input.ftpcommissionvalue2");
                    }

                    $set = [
                        "whitelabel_id" => $whitelabel['id'],
                        "name" => $val->validated("input.name"),
                        "commission_value_manager" => $commission_value_manager,
                        "commission_value_2_manager" => $commission_value_2_manager,
                        "ftp_commission_value_manager" => $ftp_commission_value_manager,
                        "ftp_commission_value_2_manager" => $ftp_commission_value_2_manager,
                    ];

                    $group->set($set);
                    $group->save();

                    Lotto_Helper::clear_cache("whitelabel_aff_group.wlgroups." . $whitelabel['id']);
                } else {
                    // This is for default group (values within whitelabel)
                    $def_commission_value_manager = null;
                    if (!empty($val2->validated("input.commissionvalue"))) {
                        $def_commission_value_manager = $val2->validated("input.commissionvalue");
                    }

                    $def_commission_value_2_manager = null;
                    if (!empty($val2->validated("input.commissionvalue2"))) {
                        $def_commission_value_2_manager = $val2->validated("input.commissionvalue2");
                    }

                    $def_ftp_commission_value_manager = null;
                    if (!empty($val->validated("input.ftpcommissionvalue"))) {
                        $def_ftp_commission_value_manager = $val->validated("input.ftpcommissionvalue");
                    }

                    $def_ftp_commission_value_2_manager = null;
                    if (!empty($val->validated("input.ftpcommissionvalue2"))) {
                        $def_ftp_commission_value_2_manager = $val->validated("input.ftpcommissionvalue2");
                    }

                    $set = [
                        "def_commission_value_manager" => $def_commission_value_manager,
                        "def_commission_value_2_manager" => $def_commission_value_2_manager,
                        "def_ftp_commission_value_manager" => $def_ftp_commission_value_manager,
                        "def_ftp_commission_value_2_manager" => $def_ftp_commission_value_2_manager,
                    ];

                    $new_whitelabel = Model_Whitelabel::find_by_pk($whitelabel['id']);
                    $new_whitelabel->set($set);
                    $new_whitelabel->save();

                    Lotto_Helper::clear_cache(["model_whitelabel.bydomain." . str_replace(".", "-", $whitelabel['domain'])]);
                }

                Session::set_flash("message", ["success", _("Affiliate group has been saved!")]);
            } else {
                $errors = Lotto_Helper::generate_errors($val2->error());
                $this->inside->set("errors", $errors);

                return self::RESULT_WITH_ERRORS;
            }
        } else {
            $errors = Lotto_Helper::generate_errors($val->error());
            $this->inside->set("errors", $errors);

            return self::RESULT_WITH_ERRORS;
        }

        return self::RESULT_OK;
    }
}
