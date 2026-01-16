<?php

namespace Fuel\Tasks\Seeders;

/**
 * Mail Templates Fixes seeder.
 */
final class Multi_Draws_Email extends Seeder
{
    public function execute(): void
    {
        $additional_translation = 'a:9:{s:14:"ticket_details";a:2:{s:5:"label";s:19:"Ticket details text";s:11:"translation";s:14:"Ticket details";}s:9:"draw_date";a:2:{s:5:"label";s:14:"Draw date text";s:11:"translation";s:9:"Draw date";}s:13:"purchase_date";a:2:{s:5:"label";s:18:"Purchase date text";s:11:"translation";s:13:"Purchase date";}s:14:"purchase_price";a:2:{s:5:"label";s:19:"Purchase price text";s:11:"translation";s:14:"Purchase price";}s:11:"ticket_type";a:2:{s:5:"label";s:16:"Ticket type text";s:11:"translation";s:11:"Ticket type";}s:22:"multi_draw_ticket_type";a:2:{s:5:"label";s:24:"Ticket type - Multi Draw";s:11:"translation";s:10:"Multi Draw";}s:25:"single_ticket_ticket_type";a:2:{s:5:"label";s:27:"Ticket type - Single Ticket";s:11:"translation";s:13:"Single ticket";}s:5:"draws";a:2:{s:5:"label";s:28:"Draws text (number of draws)";s:11:"translation";s:5:"Draws";}s:6:"button";a:2:{s:5:"label";s:11:"Button text";s:11:"translation";s:12:"View details";}}';
        \DB::query("update `mail_template` set `additional_translates`='".$additional_translation."' where `slug`='ticket-buy'")
            ->execute();
    }

    protected function columnsStaging(): array
    {
        return [
        ];
    }

    protected function rowsStaging(): array
    {
        return [
        ];
    }
}
