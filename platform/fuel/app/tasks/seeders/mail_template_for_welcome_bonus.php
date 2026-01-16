<?php

namespace Fuel\Tasks\Seeders;

/**
 * Description of Mail_Template_For_Welcome_Bonus
 */
final class Mail_Template_For_Welcome_Bonus extends Seeder
{
    protected function columnsStaging(): array
    {
        return [
            'mail_template' => ['slug', 'title', 'content', 'additional_translates', 'is_partial']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'mail_template' => [
                [
                    "welcome-bonus",
                    "You have received a bonus!",
                    "<p style=\"text-align: center;line-height:22px;font-size:14px; font-family: \'Roboto\', Arial;color:#505050;margin-top: 12px;margin-bottom:30px;\">You have received a free ticket with your first order!</p>\r\n<p style=\"text-align: center;margin-top:30px;\">{numbers}</p>",
                    null,
                    1
                ],
            ]
        ];
    }
}
