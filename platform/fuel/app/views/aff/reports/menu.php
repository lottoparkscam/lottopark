<?php

/**
 * This function will create menu array for specific menu;
 * @param int $type @see controlers\aff consts.
 * @param int $subaff_id @see controlers\aff view globals.
 * @return array menu.
 */
function getMenu($type, $subaff_id) {
    // return proper menu, depending on type
    switch ($type) {
        default:
        case Controller_Aff::AFFILIATE_REPORTS:
            return [
                ["title" => _("Payouts"), "action" => "payouts", "params" => null],
                ["title" => _("Generate report"), "action" => "reports", "params" => null],
                ["title" => _("Leads"), "action" => "leads", "params" => null],
                ["title" => _("First-Time Purchases"), "action" => "ftps", "params" => null],
                ["title" => _("Commissions"), "action" => "commissions", "params" => null],
                ['title' => _('Casino commissions'), 'action' => 'casinoCommissions', 'params' => null],
            ];
        case Controller_Aff::SUBAFFILIATE_REPORTS:
            return [
                ["title" => _("List"), "action" => "subaffiliates", "params" => null],
                ["title" => _("Generate report"), "action" => "reports", "params" => "/subaffiliates/$subaff_id"],
                ["title" => _("Leads"), "action" => "leads", "params" => "/subaffiliates/$subaff_id"],
                ["title" => _("First-Time Purchases"), "action" => "ftps", "params" => "/subaffiliates/$subaff_id"],
                ["title" => _("Commissions"), "action" => "commissions", "params" => "/subaffiliates/$subaff_id"],
                ['title' => _('Casino commissions'), 'action' => 'casinoCommissions', 'params' => "/subaffiliates/$subaff_id"],
            ];
    }
}

/**
 * This function will print menu.
 * @param array menu array. Can and should be fetched by getMenu from the same scope.
 * @param string current action (selected menu item).
 */
function printMenu($menu, $action) { // TODO: think if there is need to compare global params. I assume that comparing actions is sufficient.
    echo "<ul class=\"nav nav-pills nav-stacked\">";
    foreach ($menu as $menuItem) {
        $itemActive = ($action === $menuItem["action"]) ? 'class="active"' : '';
        $itemTitle = _($menuItem["title"]);
        echo    "<li role=\"presentation\" $itemActive>
                    <a href=\"/{$menuItem["action"]}{$menuItem["params"]}\">$itemTitle</a>
                </li>";
    }
    echo "</ul>";
}

printMenu(getMenu($reports_type, $subaff_id), $action);

    
    
    
    
   
