<?php

use Fuel\Core\Route;
use Fuel\Core\Config;
use Fuel\Core\Input;

Config::load("platform", true); // Load platform.php file to 'platform' group
$adm_subdomain = Config::get("platform.admin.subdomain");

// Remove "www" from url if there is such string
$domain = explode('.', Input::server('HTTP_HOST', ''));
if ($domain[0] == "www") {
    array_shift($domain);
}

// admin.whitelotto.com (new CRM)
if ($domain[0] === "admin") {
    Lotto_Settings::getInstance()->set("routing_source", "admin");
    // admin routes
    return [
        '_404_' => 'crm/404',
        '_root_' => 'crm/index',
        'authenticate' => [["POST", new Route('authentication/authenticate')]],
        'checklogged' => [["GET", new Route('crm/check_middleware_auth')]],
        'langsrolestimezones' => [["GET", new Route('crm/languages_roles_and_timezones')]],
        'users_view_data' => [["POST", new Route('crm/users_view_data')]],
        'add/newadmin' => [["POST", new Route('crm/new_admin_user')]],
        'update/admin' => [["POST", new Route('crm/update_admin_user')]],
        'userprofile' => [["GET", new Route('crm/current_admin_user')]],
        'modules' => [["GET", new Route('crm/accessible_modules')]],
        'allusers' => [["GET", new Route('crm/admin_users')]],
        'admin/details' => [["POST", new Route('crm/admin_user_details')]],
        'admin/delete' => [["POST", new Route('crm/delete_admin_user')]],
        'update_visible_columns' => [["POST", new Route('crm/update_users_columns')]],
        'userslist' => [["POST", new Route('crm/whitelabel_users_list')]],
        'whitelabel/users/export' => [["POST", new Route('crm/export_whitelabel_users')]],
        'usersstats' => [["POST", new Route('crm/whitelabel_users_stats')]],
        'user/details' => [["POST", new Route('crm/user_details')]],
        'user/delete' => [["POST", new Route('crm/delete_whitelabel_user')]],
        'user/restore' => [["POST", new Route('crm/restore_whitelabel_user')]],
        'user/activate' => [["POST", new Route('crm/activate_whitelabel_user')]],
        'user/confirm' => [["POST", new Route('crm/confirm_whitelabel_user')]],
        'user/countries_timezones' => [["POST", new Route('crm/whitelabel_user_countries_timezones')]],
        'user/edit_details' => [["POST", new Route('crm/whitelabel_user_edit_details')]],
        'user/regions' => [["POST", new Route('crm/user_country_regions')]],
        'user/edit/email' => [["POST", new Route('crm/user_edit_email')]],
        'user/email' => [["POST", new Route('crm/user_get_email')]],
        'user/balance_email' => [["POST", new Route('crm/user_get_balance_and_email')]],
        'user/affiliate_email' => [["POST", new Route('crm/user_get_affiliate_and_email')]],
        'user/email_currency_methods' => [["POST", new Route('crm/user_get_email_currency_payment_methods')]],
        'user/edit/password' => [["POST", new Route('crm/user_edit_password')]],
        'user/edit/balance' => [["POST", new Route('crm/user_edit_balance')]],
        'user/edit/affiliate' => [["POST", new Route('crm/user_edit_affiliate')]],
        'user/manual_deposit' => [["POST", new Route('crm/user_manual_deposit')]],
        'update/user' => [["POST", new Route('crm/update_whitelabel_user_details')]],
        'dashboard/data' => [["POST", new Route('crm/dashboard_data')]],
        'crm/tickets/table_data' => [["POST", new Route('crm/tickets_table_data')]],
        'crm/tickets/lotteries_data' => [["POST", new Route('crm/tickets_lotteries_data')]],
        'crm/tickets/lines' => [["POST", new Route('crm/tickets_lines')]],
        'crm/tickets/details' => [["POST", new Route('crm/ticket_details')]],
        'crm/tickets/export' => [["POST", new Route('crm/export_tickets')]],
        'crm/tickets/paidout' => [["POST", new Route('crm/tickets_mark_paid_out')]],
        'crm/tickets/payout' => [["POST", new Route('crm/ticket_line_payout')]],
        'crm/multidraw_tickets/table_data' => [["POST", new Route('crm/multidraw_tickets_table_data')]],
        'crm/multidraw_tickets/export' => [["POST", new Route('crm/export_multidraw_tickets')]],
        'crm/transactions/data_date_range' => [["POST", new Route('crm/transactions_data_date_range')]],
        'crm/transactions/table_data' => [["POST", new Route('crm/transactions_table_data')]],
        'crm/withdrawals/table_data' => [["POST", new Route('crm/withdrawals_table_data')]],
        'crm/transaction/details' => [["POST", new Route('crm/transaction_details')]],
        'crm/withdrawal/decline' => [["POST", new Route('crm/decline_withdrawal')]],
        'crm/withdrawal/approve' => [["POST", new Route('crm/approve_withdrawal')]],
        'crm/transactions/export' => [["POST", new Route('crm/export_transactions')]],
        'crm/withdrawals/export' => [["POST", new Route('crm/export_withdrawals')]],
        'crm/withdrawals/data' => [["POST", new Route('crm/withdrawals_data')]],
        'crm/withdrawals/data_date_range' => [["POST", new Route('crm/withdrawals_data_date_range')]],
        'crm/withdrawal/details' => [["POST", new Route('crm/withdrawal_details')]],
        'whitelabel_user_groups' => [["POST", new Route('crm/whitelabel_user_groups')]],
        'whitelabel_user_groups/change' => [["POST", new Route('crm/default_user_group_change')]],
        'whitelabel_user_groups/details' => [["POST", new Route('crm/user_group_details')]],
        'whitelabel_user_groups/update' => [["POST", new Route('crm/user_group_update')]],
        'whitelabel_user_groups/new' => [["POST", new Route('crm/new_user_group')]],
        'whitelabel_user_groups/delete' => [["POST", new Route('crm/delete_user_group')]],
        'whitelabel/users/update_groups' => [["POST", new Route('crm/update_user_groups')]],
        'crm/raffle_tickets/table_data' => [["POST", new Route('crm/raffle_tickets_table_data')]],
        'crm/raffle_tickets/export' => [["POST", new Route('crm/export_raffle_tickets')]],
        'crm/raffle_tickets/details' => [["POST", new Route('crm/raffle_ticket_details')]],
        'crm/logs_actions' => [["POST", new Route('crm/logs_actions')]],
        'crm/casino_report' => [["POST", new Route('crm/casino_report')]],
        'crm/acceptance_rate_report' => [['POST', new Route('crm/acceptance_rate_report')]],
        'crm/transaction_per_method' => [['POST', new Route('crm/transaction_per_method')]],
        'crm/withdrawals/withdrawal_report_per_method' => [['POST', new Route('crm/withdrawal_report_per_method')]],
        '403' => 'crm/index',
        '503' => 'crm/index',
        'new' => 'crm/index',
        'profile' => 'crm/index',
        'login' => 'crm/index',
        'admin/users' => 'crm/index',
        'admin/users/(:id)' => 'crm/index',
        'whitelabel/users' => 'crm/index',
        'whitelabel/users/view/(:id)' => 'crm/index',
        'whitelabel/users/edit/(:id)' => 'crm/index',
        'crm/tickets' => 'crm/index',
        'crm/tickets/(:id)' => 'crm/index',
        'crm/multidraw_tickets' => 'crm/index',
        'crm/transactions/lottery' => 'crm/index',
        'crm/transactions/casino' => 'crm/index',
        'crm/deposits/lottery' => 'crm/index',
        'crm/deposits/casino' => 'crm/index',
        'crm/transactions/lottery/view/(:id)' => 'crm/index',
        'crm/transactions/casino/view/(:id)' => 'crm/index',
        'crm/deposits/lottery/view/(:id)' => 'crm/index',
        'crm/deposits/casino/view/(:id)' => 'crm/index',
        'crm/withdrawals/lottery/iew/(:id)' => 'crm/index',
        'crm/withdrawals/casino/view/(:id)' => 'crm/index',
        'crm/withdrawals/lottery' => 'crm/index',
        'crm/withdrawals/casino' => 'crm/index',
        'whitelabel/users/groups' => 'crm/index',
        'crm/reports/casino' => 'crm/index',
        'crm/reports/acceptance_rate' => 'crm/index',
        'crm/logs' => 'crm/index',
        'whitelabel/users/groups/new' => 'crm/index',
        'whitelabel/users/groups/(:action)/(:id)' => 'crm/index',
        'crm/raffle_tickets' => 'crm/index',
        'crm/raffle_tickets/:id' => 'crm/index',
        'crm/settings/casino' => 'crm/index',
        'crm/settings/casino/games-order' => 'crm/index',
        'crm/seo-widgets/generator' => 'crm/index',
        'crm/draws/ltech-manual-draws' => 'crm/index',
        'api/doc' => 'crm/index',
        'crm/scan' => [['GET', new Route('crm/scan')]],
    ];
}

// manager.<whitelabeldomain>.<tld> (old "CRM")
if ($domain[0] === "manager") {
    Lotto_Settings::getInstance()->set("routing_source", "manager");
    // manager routes
    return [
        '_404_' => 'index/404', // The main 404 route
        '_root_' => 'whitelabel/index',
        'users(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/users',
        'deleted(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/users/deleted',
        'inactive(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/users/inactive',
        'transactions(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/transactions',
        'deposits(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/transactions/deposits',
        'withdrawals(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/withdrawals',
        'tickets(/:action)?(/:id)?(/s/:page)?(/:subaction)?(/:sid)?' => 'whitelabel/tickets',
        'multidraw_tickets(/:action)?(/:id)?(/s/:page)?(/:subaction)?(/:sid)?' => 'whitelabel/multidraw_tickets',
        'slip/(:image)' => "whitelabel/slip",
        'winners(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/winners',
        'reports(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/reports',
        'analytics(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/analytics',
        'fbpixel(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/fbpixel',
        'paymentmethods(/:action)?(/:id)?(/s/:page)?(/:subaction)?(/:edit_id)?' => 'whitelabel/paymentmethods',
        'ccsettings(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/ccsettings',
        'settings(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/settings',
        'lotterysettings(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/lotterysettings',
        'multidrawsettings(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/multidrawsettings',
        'mailsettings(/:action)?(/:id)?(/:lang)?' => 'whitelabel/mailsettings',
        'blocked_countries(/:subaction)?(/:code)?' => 'whitelabel/blocked_countries',
        'affs(/:rparam)?(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/affs',
        'account(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/account',
        'ajax/password' => 'whitelabel/ajaxpassword',
        'ajax/insurance' => 'whitelabel/insurance',
        'signout' => 'whitelabel/signout',
        'prepaid(/s/:page)?' => 'whitelabel/prepaid',
        'settings_currency(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/settings_currency',
        'settings_country_currency(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/settings_country_currency',
        'bonuses(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'whitelabel/bonuses',
    ];
}

// aff.<whitelabeldomain>.<tld> (affiliate panel)
if ($domain[0] === "aff") {
    Lotto_Settings::getInstance()->set("routing_source", "aff");
    // affiliate panel routes
    return [
        '_404_' => 'index/404', // The main 404 route
        '_root_' => 'aff/index',
        'reports(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'aff/reports',
        'leads(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'aff/leads',
        'ftps(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'aff/ftps',
        'analytics(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'aff/analytics',
        'fbpixel(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'aff/fbpixel',
        'commissions(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'aff/commissions',
        'casinoCommissions(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'aff/casinoCommissions',
        'payouts(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'aff/payouts',
        'settings(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'aff/settings',
        'payment(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'aff/payment',
        'signout' => 'aff/signout',
        'subaffiliates(/:subaction)?(/:id)?' => 'aff/subaffiliates',
        'banners' => 'aff/banners',
        'widgets' => 'aff/widgets',
        'sign_up' => 'aff/sign_up',
        'password/lost(/:hash)?' => 'aff/password/lost_password',
        'activation(/:token)?(/:hash)?' => 'aff/activation',
        'resend(/:token)?(/:hash)?' => 'aff/resend',
    ];
}

// api.<whitelabeldomain>.<tld> (API to be used on external landing pages)
if ($domain[0] === "api") {
    Lotto_Settings::getInstance()->set("routing_source", "api");
    // api panel routes
    return [
        '_404_' => 'api/error/404',
        'api/lottery' => 'api/lottery/lottery',
        'api/lotteries' => 'api/lottery/lotteries',
        'api/lotteries_mautic' => 'api/lottery/lotteries_mautic',
        'api/payments/zen/generateRedirectUrl' => 'api/zen/generate_redirect_url',
        'api/checkouts' => 'api/checkouts/checkouts',
        'api/checkouts/confirm' => 'api/checkouts/confirm',
        'api/checkouts/success' => 'api/checkouts/success',
        'api/checkouts/failure' => 'api/checkouts/failure',
    ];
}

// empire.whitelotto.com (old super-admin)
if ($domain[0] === $adm_subdomain) {
    Lotto_Settings::getInstance()->set("routing_source", "empire");
    // empire panel routes
    // includes: task, dev, admin controllers
    return [
        '_404_' => 'index/404', // The main 404 route
        '_root_' => 'admin/index',
        'task/thelotter_scan_confirm' => 'task/thelotter_scan_confirm',
        'paymentlogs(/s/:page)?' => 'admin/paymentlogs',
        'lotteries/delays(/:action)?(/:id)?(/s/:page)?' => 'admin/delays',
        'lotteries/logs(/s/:page)?' => 'admin/logs',
        'lotteries/imvalaplogs(/s/:page)?' => 'admin/imvalaplogs',
        'lotteries/lottorisqlogs(/s/:page)?' => 'admin/lottorisqlogs',
        'lotteries(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'admin/lotteries',
        'whitelabels(/:action)?(/:id)?(/s/:page)?(/:subaction)?(/:sid)?(/:deeplevelaction)?(/:edit_id)?' => 'admin/whitelabels',
        'users(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'admin/users',
        'deleted(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'admin/users/deleted',
        'inactive(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'admin/users/inactive',
        'transactions(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'admin/transactions',
        'deposits(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'admin/transactions/deposits',
        'withdrawals(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'admin/withdrawals',
        'tickets(/:action)?(/:id)?(/s/:page)?(/:subaction)?(/:sid)?' => 'admin/tickets',
        'multidraw_tickets(/:action)?(/:id)?(/s/:page)?(/:subaction)?(/:sid)?' => 'admin/multidraw_tickets',
        'reports(/:action)?(/:id)?(/s/:page)?(/:subaction)?' => 'admin/reports',
        'signout' => 'admin/signout',
        'test(/:days)' => 'test/get_megasena_next_draw_response',
        'invoice' => 'admin/invoice',
        'marketing-tools' => 'admin/marketingTools',
    ];
}

return [
    // wordpress routes are handled by wordpress
];
