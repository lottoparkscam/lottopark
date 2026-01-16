<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.8
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2016 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your app/config folder, and make them in there.
 *
 * This will allow you to upgrade fuel without losing your custom config.
 */

$setup_name = 'default';

/**
 * ------------ BEGIN OF DEFAULT ARRAYS
 * Those are default settings for custom configuration.
 * You can put copy of each of array under custom case and setup it there
 * See $smtp_settings array under vodilotto.com case.
 */
$driver = 'mail';

$smtp_settings = [
    'host'     => $_ENV['MAIL_SMTP_HOST'],
    'port'     => $_ENV['MAIL_SMTP_PORT'],
    'username' => '',
    'password' => '',
    'timeout'  => 5,
    'starttls' => false,
];

$mandrill_settings = [
    'key' => 'api_key',
    'message_options' => [],
    'send_options' => [
        'async'   => false,
        'ip_pool' => null,
        'send_at' => null,
    ],
];

$mailgun_settings = [
   'key'    => 'api_key',
   'domain' => 'domain',
];
/**
 * ------------ END OF DEFUALT ARRAYS
 */

// MAIN SETTINGS ARRAY
$settings = [

    /**
     * Default setup group
     */
    'default_setup' => $setup_name,

    /**
     * Default setup groups
     */
    'setups' => [
        'default' => ['driver' => $_ENV['MAIL_DRIVER'], 'smtp' => $smtp_settings, 'newline' => $_ENV['MAIL_NEWLINE']],
    ],

    /**
     * Default settings
     */
    'defaults' => [

        /**
         * Mail useragent string
         */
        'useragent' => null,

        /**
         * Mail driver (can be mail, smtp, sendmail, mailgun, mandrill or noop (only logs writes to log))
         */
        'driver' => 'mail',

        /**
         * Whether to send as html, set to null for autodetection.
         */
        'is_html' => null,

        /**
         * Email charset
         */
        'charset' => 'utf-8',

        /**
         * Whether to encode subject and recipient names.
         * Requires the mbstring extension: http://www.php.net/manual/en/ref.mbstring.php
         */
        'encode_headers' => true,

        /**
         * Ecoding (8bit, base64 or quoted-printable)
         */
        'encoding' => '8bit',

        /**
         * Email priority
         */
        'priority' => \Email::P_NORMAL,

        /**
         * Default sender details
         */
        'from' => [
            'email' => false,
            'name'  => false,
        ],

        /**
         * Whether to validate email addresses
         */
        'validate' => true,

        /**
         * Auto attach inline files
         */
        'auto_attach' => true,

        /**
         * Auto generate alt body from html body
         */
        'generate_alt' => true,

        /**
         * Forces content type multipart/related to be set as multipart/mixed.
         */
        'force_mixed' => false,

        /**
         * Wordwrap size, set to null, 0 or false to disable wordwrapping
         */
        'wordwrap' => 76,

        /**
         * Path to sendmail
         */
        'sendmail_path' => '/usr/sbin/sendmail',

        /**
         * SMTP settings
         */
        'smtp' => [
            'host'     => '',
            'port'     => 25,
            'username' => '',
            'password' => '',
            'timeout'  => 5,
            'starttls' => false,
        ],

        /**
         * Newline
         */
        'newline' => "\n",

        /**
         * Attachment paths
         */
        'attach_paths' => [
            '', 		// absolute path
            DOCROOT, 	// relative to docroot.
        ],

        /**
         * Default return path
         */
        'return_path' => false,

        /**
         * Remove html comments
         */
        'remove_html_comments' => true,

        /**
         * Mandrill settings, see http://mandrill.com/
         */
        'mandrill' => [
            'key' => 'api_key',
            'message_options' => [],
            'send_options' => [
                'async'   => false,
                'ip_pool' => null,
                'send_at' => null,
            ],
        ],

        /**
         * Mailgun settings, see http://www.mailgun.com/
         */
        'mailgun' => [
            'key'    => 'api_key',
            'domain' => 'domain',
        ],

        /**
         * When relative protocol uri's ("//uri") are used in the email body,
         * you can specify here what you want them to be replaced with. Options
         * are "http://", "https://" or \Input::protocol() if you want to use
         * whatever was used to request the controller.
         */
        'relative_protocol_replacement' => false,
    ],
];

return $settings;
