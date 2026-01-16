<?php

namespace Fuel\Tasks\Seeders;

final class Mail_Template_For_Referafriend extends Seeder
{
    protected function columnsStaging(): array
    {
        return [
            'mail_template' => ['slug', 'title', 'content', 'text_content', 'additional_translates', 'is_partial']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'mail_template' => [
                [
                    "refer-to-bonus",
                    "{name} - You have received a bonus!",
                    "<table width=\"100%\"><tbody><tr><td style=\"line-height: 150%; font-size: 19px; font-family: Roboto, Arial; color: #505050;\" align=\"left\">You have received a free ticket, because you have registered using your friend's link!</td></tr></tbody></table><table width=\"100%\" align=\"center\"><tbody><tr><td style=\"padding-top: 30px;padding-bottom:30px;\" align=\"center\">{numbers}</td></tr></tbody></table><table width=\"100%\" align=\"center\" style=\"border-top:1px solid #E8E8E8;\"><tbody><tr><td style=\"line-height: 150%; font-size: 16px; font-family: Roboto, Arial; color: #727e8b;padding-top:2px;font-weight:bold;padding-top:30px;\" align=\"left\">What you can do now?</td></tr><tr><td style=\"line-height: 150%; font-size: 16px; font-family: Roboto, Arial; color: #727e8b;padding-top:1px;\" align=\"left\">You can check the details of all your purchased tickets anytime. Simply click the button below.</td></tr></tbody></table><table width=\"100%\" align=\"center\"><tbody><tr><td style=\"padding-top: 30px;padding-bottom:15px;\" align=\"center\">{button}</td></tr></tbody></table>",
                    "You have received a free ticket, because you have registered using your friend's link!\r\n\r\n{numbers}",
                    "a:4:{s:14:\"ticket_details\";a:2:{s:5:\"label\";s:19:\"Ticket details text\";s:11:\"translation\";s:14:\"Ticket details\";}s:9:\"draw_date\";a:2:{s:5:\"label\";s:14:\"Draw date text\";s:11:\"translation\";s:9:\"Draw date\";}s:13:\"purchase_date\";a:2:{s:5:\"label\";s:23:\"Bonus receive date text\";s:11:\"translation\";s:17:\"Bonus received on\";}s:6:\"button\";a:2:{s:5:\"label\";s:13:\"Action button\";s:11:\"translation\";s:15:\"view my tickets\";}}",
                    1
                ],
                [
                    "refer-by-bonus",
                    "{name} - You have received a bonus!",
                    "<table width=\"100%\"><tbody><tr><td style=\"line-height: 150%; font-size: 19px; font-family: Roboto, Arial; color: #505050;\" align=\"left\">Somebody used your link to register and you have received a free ticket!</td></tr></tbody></table><table width=\"100%\" align=\"center\"><tbody><tr><td style=\"padding-top: 30px;padding-bottom:30px;\" align=\"center\">{numbers}</td></tr></tbody></table><table width=\"100%\" align=\"center\" style=\"border-top:1px solid #E8E8E8;\"><tbody><tr><td style=\"line-height: 150%; font-size: 16px; font-family: Roboto, Arial; color: #727e8b;padding-top:2px;font-weight:bold;padding-top:30px;\" align=\"left\">What you can do now?</td></tr><tr><td style=\"line-height: 150%; font-size: 16px; font-family: Roboto, Arial; color: #727e8b;padding-top:1px;\" align=\"left\">You can check the details of all your purchased tickets anytime. Simply click the button below.</td></tr></tbody></table><table width=\"100%\" align=\"center\"><tbody><tr><td style=\"padding-top: 30px;padding-bottom:15px;\" align=\"center\">{button}</td></tr></tbody></table>",
                    "Somebody used your link to register and you have received a free ticket!\r\n\r\n{numbers}",
                    "a:4:{s:14:\"ticket_details\";a:2:{s:5:\"label\";s:19:\"Ticket details text\";s:11:\"translation\";s:14:\"Ticket details\";}s:9:\"draw_date\";a:2:{s:5:\"label\";s:14:\"Draw date text\";s:11:\"translation\";s:9:\"Draw date\";}s:13:\"purchase_date\";a:2:{s:5:\"label\";s:23:\"Bonus receive date text\";s:11:\"translation\";s:17:\"Bonus received on\";}s:6:\"button\";a:2:{s:5:\"label\";s:13:\"Action button\";s:11:\"translation\";s:15:\"view my tickets\";}}",
                    1
                ],
            ]
        ];
    }
}
