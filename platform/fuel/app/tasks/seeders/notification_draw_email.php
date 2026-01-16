<?php

namespace Fuel\Tasks\Seeders;

/**
 * Mail Templates Fixes seeder.
 */
final class Notification_Draw_Email extends Seeder
{
    public function execute(): void
    {
        \DB::query("INSERT INTO `mail_template` (`id`, `slug`, `title`, `content`, `additional_translates`, `is_partial`) VALUES
        (null, 'draw-notification', '{name} - Check draw results!', '<table width=\"100%\">\r\n<tbody>\r\n<tr>\r\n<td style=\"line-height: 22px; font-size: 19px; font-family: Roboto, Arial; color: #505050; padding-bottom: 28px;\" align=\"center\">Thank you for participating in the latest draw! Here are the draw results:</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table style=\"margin-left: auto; margin-right: auto;\">\r\n<tbody>\r\n<tr>\r\n<td style=\"padding-right: 50px;line-height: 22px; font-size: 19px; font-family: Roboto, Arial; color: #505050;\"><strong>Lottery: </strong></td>\r\n<td style=\"line-height: 22px; font-size: 19px; font-family: Roboto, Arial; color: #505050;\">{lottery_name}</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table width=\"100%\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"padding-top: 25px;\" align=\"center\">{numbers}</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<table width=\"100%\" align=\"center\">\r\n<tbody>\r\n<tr>\r\n<td style=\"padding-top: 30px;\" align=\"center\">{button}</td>\r\n</tr>\r\n</tbody>\r\n</table>', 'a:1:{s:6:\"button\";a:2:{s:5:\"label\";s:34:\"Check your winnings button content\";s:11:\"translation\";s:19:\"Check your winnings\";}}', 1);
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
