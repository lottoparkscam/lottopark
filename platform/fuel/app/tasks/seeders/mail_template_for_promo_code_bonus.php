<?php

namespace Fuel\Tasks\Seeders;

final class Mail_Template_For_Promo_Code_Bonus extends Seeder
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
                    "promo-code-bonus",
                    "{name} - You have received a bonus!",
                    '<table width="100%">
                    <tbody>
                    <tr>
                    <td style="padding-bottom: 25px; line-height: 150%; font-size: 19px; font-family: Roboto, Arial; color: #505050;" align="left">Congratulations! You have received a free ticket for {lottery_name} as a bonus!</td>
                    </tr>
                    </tbody>
                    </table>
                    <table style="border-top: 1px solid #E8E8E8;" width="100%" align="center">
                    <tbody>
                    <tr>
                    <td style="line-height: 150%; font-size: 16px; font-family: Roboto, Arial; color: #727e8b; padding-top: 30px; font-weight: bold;" align="left">What you can do now?</td>
                    </tr>
                    <tr>
                    <td style="line-height: 150%; font-size: 16px; font-family: Roboto, Arial; color: #727e8b; padding-top: 1px;" align="left">You can check the details of all your purchased tickets anytime. Simply click the button below.</td>
                    </tr>
                    </tbody>
                    </table>
                    <table width="100%" align="center">
                    <tbody>
                    <tr>
                    <td style="padding-top: 30px; padding-bottom: 15px;" align="center">{button}</td>
                    </tr>
                    </tbody>
                    </table>',
                    "Congratulations! You have received a free ticket for {lottery_name} as a bonus! What you can do now? You can check the details of all your purchased tickets anytime. Simply click the button below. {button}",
                    'a:1:{s:6:"button";a:2:{s:5:"label";s:13:"Action button";s:11:"translation";s:15:"view my tickets";}}',
                    1
                ],
            ]
        ];
    }
}
