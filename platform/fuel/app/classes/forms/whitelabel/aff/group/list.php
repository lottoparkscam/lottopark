<?php

use Fuel\Core\View;

/**
 * @deprecated
 * Description of Forms_Whitelabel_Aff_Group_List
 */
class Forms_Whitelabel_Aff_Group_List
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
     * @return array
     */
    private function prepare_default_group_data(): array
    {
        $whitelabel = $this->get_whitelabel();

        $default_group_data = [];

        if (!empty($whitelabel['def_commission_value_manager'])) {
            $text_to_show = _("1st-tier sale commission value");
            $text_to_show .= ": ";
            $def_com_percentage = round(
                $whitelabel['def_commission_value_manager'] / 100,
                Helpers_Currency::RATE_SCALE
            );
            $text_to_show .= Lotto_View::format_percentage($def_com_percentage);
            $default_group_data['commission_value_manager'] = $text_to_show;
        }

        if (!empty($whitelabel['def_commission_value_2_manager'])) {
            $text_to_show = _("2nd-tier sale commission value");
            $text_to_show .= ": ";
            $def_com_percentage = round(
                $whitelabel['def_commission_value_2_manager'] / 100,
                Helpers_Currency::RATE_SCALE
            );
            $text_to_show .= Lotto_View::format_percentage($def_com_percentage);
            $default_group_data['commission_value_2_manager'] = $text_to_show;
        }

        if (!empty($whitelabel['def_ftp_commission_value_manager'])) {
            $text_to_show = _("1st-tier First Time Purchase commission value");
            $text_to_show .= ": ";
            $def_com_percentage = round(
                $whitelabel['def_ftp_commission_value_manager'] / 100,
                Helpers_Currency::RATE_SCALE
            );
            $text_to_show .= Lotto_View::format_percentage($def_com_percentage);
            $default_group_data['ftp_commission_value_manager'] = $text_to_show;
        }

        if (!empty($whitelabel['def_ftp_commission_value_2_manager'])) {
            $text_to_show = _("2nd-tier First Time Purchase commission value");
            $text_to_show .= ": ";
            $def_com_percentage = round(
                $whitelabel['def_ftp_commission_value_2_manager'] / 100,
                Helpers_Currency::RATE_SCALE
            );
            $text_to_show .= Lotto_View::format_percentage($def_com_percentage);
            $default_group_data['ftp_commission_value_2_manager'] = $text_to_show;
        }

        return $default_group_data;
    }

    /**
     *
     * @param int $id
     * @return array
     */
    private function prepare_other_group_data_for_id(int $id): array
    {
        $group = Model_WhitelabeL_Aff_Group::find_by_pk($id);

        $other_group_data = [];

        if (!empty($group['commission_value_manager'])) {
            $text_to_show = _("1st-tier sale commission value");
            $text_to_show .= ": ";
            $def_com_percentage = round(
                $group['commission_value_manager'] / 100,
                Helpers_Currency::RATE_SCALE
            );
            $text_to_show .= Lotto_View::format_percentage($def_com_percentage);

            $other_group_data['commission_value_manager'] = $text_to_show;
        }

        if (!empty($group['commission_value_2_manager'])) {
            $text_to_show = _("2nd-tier sale commission value");
            $text_to_show .= ": ";
            $def_com_percentage = round(
                $group['commission_value_2_manager'] / 100,
                Helpers_Currency::RATE_SCALE
            );
            $text_to_show .= Lotto_View::format_percentage($def_com_percentage);
            $other_group_data['commission_value_2_manager'] = $text_to_show;
        }

        if (!empty($group['ftp_commission_value_manager'])) {
            $text_to_show = _("1st-tier First Time Purchase commission value");
            $text_to_show .= ": ";
            $def_com_percentage = round(
                $group['ftp_commission_value_manager'] / 100,
                Helpers_Currency::RATE_SCALE
            );
            $text_to_show .= Lotto_View::format_percentage($def_com_percentage);
            $other_group_data['ftp_commission_value_manager'] = $text_to_show;
        }

        if (!empty($group['ftp_commission_value_2_manager'])) {
            $text_to_show = _("2nd-tier First Time Purchase commission value");
            $text_to_show .= ": ";
            $def_com_percentage = round(
                $group['ftp_commission_value_2_manager'] / 100,
                Helpers_Currency::RATE_SCALE
            );
            $text_to_show .= Lotto_View::format_percentage($def_com_percentage);
            $other_group_data['ftp_commission_value_2_manager'] = $text_to_show;
        }

        return $other_group_data;
    }

    /**
     *
     * @return array
     */
    private function prepare_other_groups_data(): array
    {
        $whitelabel = $this->get_whitelabel();
        $groups = Model_Whitelabel_Aff_Group::get_whitelabel_groups($whitelabel);
        $other_groups_data = [];
        $start_url = "/affs/lottery-groups/";

        $i = 0;
        foreach ($groups as $group) {
            $i++;

            $other_group_data = [];
            $other_group_data['index'] = $i;
            $other_group_data['name'] = $group['name'];

            if (!empty($group['commission_value_manager'])) {
                $text_to_show = _("1st-tier sale commission value");
                $text_to_show .= ": ";
                $def_com_percentage = round(
                    $group['commission_value_manager'] / 100,
                    Helpers_Currency::RATE_SCALE
                );
                $text_to_show .= Lotto_View::format_percentage($def_com_percentage);
                $other_group_data['commission_value_manager'] = $text_to_show;
            }

            if (!empty($group['commission_value_2_manager'])) {
                $text_to_show = _("2nd-tier sale commission value");
                $text_to_show .= ": ";
                $def_com_percentage = round(
                    $group['commission_value_2_manager'] / 100,
                    Helpers_Currency::RATE_SCALE
                );
                $text_to_show .= Lotto_View::format_percentage($def_com_percentage);
                $other_group_data['commission_value_2_manager'] = $text_to_show;
            }

            if (!empty($group['ftp_commission_value_manager'])) {
                $text_to_show = _("1st-tier First Time Purchase commission value");
                $text_to_show .= ": ";
                $def_com_percentage = round(
                    $group['ftp_commission_value_manager'] / 100,
                    Helpers_Currency::RATE_SCALE
                );
                $text_to_show .= Lotto_View::format_percentage($def_com_percentage);
                $other_group_data['ftp_commission_value_manager'] = $text_to_show;
            }

            if (!empty($group['ftp_commission_value_2_manager'])) {
                $text_to_show = _("2nd-tier First Time Purchase commission value");
                $text_to_show .= ": ";
                $def_com_percentage = round(
                    $group['ftp_commission_value_2_manager'] / 100,
                    Helpers_Currency::RATE_SCALE
                );
                $text_to_show .= Lotto_View::format_percentage($def_com_percentage);
                $other_group_data['ftp_commission_value_2_manager'] = $text_to_show;
            }

            $other_group_data['edit_url'] = $start_url . "edit/" . $i;

            $other_group_data['delete_url'] = $start_url . "delete/" . $i;

            $other_groups_data[] = $other_group_data;
        }

        return $other_groups_data;
    }

    /**
     *
     * @return void
     */
    public function process_form(string $view_template): void
    {
        $default_group_data = $this->prepare_default_group_data();

        $other_groups_data = $this->prepare_other_groups_data();

        $inside = View::forge($view_template);
        $inside->set("default_group_data", $default_group_data);
        $inside->set("other_groups_data", $other_groups_data);

        $this->inside = $inside;
    }

    /**
     *
     * @param array $user Rather this is Model_Whitelabel_Aff varaiable
     * @return array
     */
    public function get_group_data(array $user = null): array
    {
        $group_data = [];
        if (!empty($user) && !empty($user['whitelabel_aff_group_id'])) {
            $group_data = $this->prepare_other_group_data_for_id($user['whitelabel_aff_group_id']);
        } else {
            $group_data = $this->prepare_default_group_data();
        }

        return $group_data;
    }
}
