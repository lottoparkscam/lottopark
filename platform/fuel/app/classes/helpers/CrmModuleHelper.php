<?php

namespace Helpers;

/**
 * Modules provide access for CRM.
 * They are located in PHP and React.
 * Changing module name here must be changed in React at the same time.
 */

final class CrmModuleHelper
{
    public const MODULE_WITHDRAWALS_EDIT = 'withdrawals-edit';
    public const MODULE_USERS_MANUAL_DEPOSIT_ADD = 'users-manual-deposit-add';
    public const MODULE_CASINO_WITHDRAWALS_EDIT = 'casino-withdrawals-edit';
    public const MODULE_CASINO_WITHDRAWALS_VIEW = 'casino-withdrawals-view';
    public const MODULE_USERS_CASINO_BALANCE_MANUAL_DEPOSIT_ADD = 'users-manual-deposit-casino-add';
    public const MODULE_USERS_EDIT_ACCOUNT_PERSONAL_DATA = 'users-edit-account-personal-data';
    public const MODULE_CASINO_DEPOSITS_VIEW = 'casino-deposits-view';
    public const MODULE_DEPOSITS_VIEW = 'deposits-view';
    public const MODULE_TRANSACTIONS_VIEW = 'transactions-view';
    public const MODULE_WITHDRAWALS_VIEW = 'withdrawals-view';
    public const MODULE_USERS_BALANCE_CASINO_EDIT = 'users-balance-casino-edit';
    public const MODULE_USERS_BONUS_BALANCE_MANUAL_DEPOSIT_ADD = 'users-bonus-balance-manual-deposit-add';
    public const MODULE_USERS_BONUS_BALANCE_EDIT = 'users-bonus-balance-edit';
    public const MODULE_USERS_BALANCE_EDIT = 'users-balance-edit';
    public const MODULE_WHITELABEL_CASINO_SETTINGS = 'whitelabel-casino-settings';
    public const MODULE_WHITELABEL_CASINO_SETTINGS_GAME_ORDER = 'whitelabel-casino-settings-game-order';
    public const MODULE_SEO_WIDGETS_GENERATOR = 'seo-widgets-generator';
}
