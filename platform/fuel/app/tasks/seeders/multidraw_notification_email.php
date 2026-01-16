<?php

namespace Fuel\Tasks\Seeders;

/**
 * Multi-draw notification e-mail
 */
final class Multidraw_Notification_Email extends Seeder
{
    public function execute(): void
    {
        $html_content = '<table width="100%">
<tbody>
<tr>
<td style="line-height: 150%; font-size: 19px; font-family: Roboto, Arial; color: #505050; padding-bottom: 23px;" align="left">Your multi-draw ticket is expiring soon. Make sure to renew it so you won\'t miss out any draws!</td>
</tr>
</tbody>
</table>
<table width="100%" align="center" cellpadding="0" cellspacing="0">
<tbody>
<tr>
<td style="padding-top: 10px;padding-bottom:25px;" align="center">{multidraw}</td>
</tr>
</tbody>
</table>
<table width="100%" align="center">
<tbody>
<tr>
<td style="padding-top: 30px;" align="center">{button}</td>
</tr>
</tbody>
</table>';

        $text_content = 'Your multi-draw ticket is expiring soon. Make sure to renew it so you won\'t miss out any draws!'."\r\n".
        "{multidraw}"."\r\n".
        "{button}";

        $serialized_content = [
            "button" => [
                "label" => "Renew Multi-Draw button content",
                "translation" => "Renew Multi-Draw"
            ],
            "draws" => [
                "label" => "Draws label",
                "translation" => "Draws"
            ],
            "last_date" => [
                "label" => "Last Date label",
                "translation" => "Last Date"
            ],
            "ticket_details" => [
                "label" => "Ticket details label",
                "translation" => "Multi-draw ticket details"
            ]
        ];

        \DB::query("INSERT INTO `mail_template` (`id`, `slug`, `title`, `content`, `text_content`, `additional_translates`, `is_partial`) VALUES
        (null, 'multidraw-notification', '{name} - Your multi-draw ticket is expiring soon!', '".addslashes($html_content)."', '".addslashes($text_content)."', '".serialize($serialized_content)."', 1);
        ")->execute();
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
