<?php

namespace Fuel\Tasks\Seeders;

final class Mail_Template_For_Support_Ticket extends Seeder
{
    private const BUTTON = [
        'button' => [
            'label' => 'Action button',
            'translation' => 'Response'
        ]
    ];
    private const DEFAULT_TEXT_STYLE = 'style="padding-bottom: 25px; line-height: 150%; font-size: 19px; font-family: Roboto, Arial; color: #505050;" align="left"';
    private const LINK_STYLE = 'style="color: #1f93d5; font-weight: bold; text-decoration: unset;"';

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
                    'support-ticket',
                    '[{language} {domain}] - {subject}',
                    '<table width="100%">
                    <tbody>
                    <tr>
                    <td ' . self::DEFAULT_TEXT_STYLE . '>You have received a new question from user:</td>
                    </tr>
                    </tbody>
                    </table>
                    <table width="100%">
                    <tbody>
                    <tr>
                    <td ' . self::DEFAULT_TEXT_STYLE . '>Name: </td>
                    <td ' . self::DEFAULT_TEXT_STYLE . '>{user_name}</td>
                    </tr>
                    <tr>
                    <td ' . self::DEFAULT_TEXT_STYLE . '>Email: </td>
                    <td ' . self::DEFAULT_TEXT_STYLE . '><a ' . self::LINK_STYLE . ' href="mailto:{user_email}"> {user_email}</a></td>
                    </tr>
                    <tr>
                    <td ' . self::DEFAULT_TEXT_STYLE . '>Phone: </td>
                    <td ' . self::DEFAULT_TEXT_STYLE . '><a ' . self::LINK_STYLE . ' href="tel:{user_phone}">{user_phone}</a></td>
                    </tr>
                    </tbody>
                    </table>
                    <table width="100%">
                    <tbody>
                    <tr>
                    <td ' . self::DEFAULT_TEXT_STYLE . '><q>{user_message}</q></td>
                    </tr>
                    </tbody>
                    </table>
                    <table style="border-top: 1px solid #E8E8E8;" width="100%" align="center">
                    <tbody>
                    <tr>
                    <td style="line-height: 150%; font-size: 16px; font-family: Roboto, Arial; color: #727e8b; padding-top: 30px; font-weight: bold;" align="left">What you can do now?</td>
                    </tr>
                    <tr>
                    <td style="line-height: 150%; font-size: 16px; font-family: Roboto, Arial; color: #727e8b; padding-top: 1px;" align="left">Click the button below to get automatic redirect to response this e-mail.</td>
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
                    'You have received a new question from user: {user_name}, {user_email}, {user_phone} 
                    Question:
                    {user_message}
                    What you can do now? Click the button below to get automatic redirect to response this e-mail. {button}',
                    serialize(self::BUTTON),
                    1
                ],
            ]
        ];
    }
}
