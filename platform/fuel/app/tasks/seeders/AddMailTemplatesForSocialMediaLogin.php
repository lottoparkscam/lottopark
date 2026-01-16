<?php

namespace Fuel\Tasks\Seeders;

class AddMailTemplatesForSocialMediaLogin extends Seeder
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
                    'confirm-social-login',
                    '{name} - Social account activation',
                    '<table width="100%">
                        <tbody>
                            <tr>
                                <td style="line-height: 1.25em; font-size: 1.25em; font-family: Roboto, Arial; color: #505050; text-align: center; padding-top: 1em;">
                                You are trying to login with {socialName}.<br>
                                If it`s you, please click here to accept login from social media.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table width="100%" align="center">
                        <tbody>
                            <tr>
                                <td style="padding-top: 30px; padding-bottom: 15px;" align="center">
                                    {button}
                                </td>
                            </tr>
                        </tbody>
                    </table>',
                    'You are trying to login with {socialName} If it&acutes you, please click here to accept login from social media. {button}',
                    'a:1:{s:6:"button";a:2:{s:5:"label";s:13:"Confirm login";s:11:"translation";s:13:"Confirm login";}}',
                    1
                ],
            ]
        ];
    }
}
