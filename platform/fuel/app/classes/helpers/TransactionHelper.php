<?php

declare(strict_types=1);

namespace Helpers;

use Helpers_General;

class TransactionHelper
{
    public static function getTypes(bool $withTranslation = true): array
    {
        return [
            Helpers_General::TYPE_TRANSACTION_PURCHASE => $withTranslation ? _('Purchase') : 'Purchase',
            Helpers_General::TYPE_TRANSACTION_DEPOSIT => $withTranslation ? _('Deposit') : 'Deposit',
        ];
    }

    public static function getStatuses(bool $withTranslation = true): array
    {
        return [
            Helpers_General::STATUS_TRANSACTION_PENDING => $withTranslation ? _('Pending') : 'Pending',
            Helpers_General::STATUS_TRANSACTION_APPROVED => $withTranslation ?_('Approved') : 'Approved',
            Helpers_General::STATUS_TRANSACTION_ERROR => $withTranslation ? _('Error') : 'Error',
        ];
    }
}
