<?php /* Template Name: Raffle Ticket Purchase */ ?>
<?php

use Fuel\Core\Config;
use Fuel\Core\Session;
use Helpers\FlashMessageHelper;
use Models\Raffle;
use Fuel\Core\Input;
use Fuel\Core\Security;
use Models\WhitelabelUser;
use Services\MailerService;
use GuzzleHttp\Exception\ServerException;
use Services\Logs\FileLoggerService;

if (!defined('WPINC')) {
    die;
}

$fileLoggerService = Container::get(FileLoggerService::class);

$numbers = empty(Input::post('numbers')) ? [] : array_filter(Input::post('numbers'), function (string $number) {
    $number = htmlspecialchars($number, ENT_QUOTES, 'UTF-8');
    return is_numeric($number);
});

$redirect = function (?string $location = null) use (&$numbers) {
    $location = !empty($location) ? $location : Input::referrer();
    if (empty(Input::referrer())) {
        wp_redirect('/');
        exit;
    }
    wp_redirect($location . '?' . http_build_query(['numbers' => $numbers]));
    exit;
};

$error_handler = function (string $message, ?Throwable $exception = null, bool $notify_slack = true, string $type = 'error') use (&$redirect, $fileLoggerService) {
    if ($notify_slack) {
        $file = !empty($exception) ? $exception->getFile() : __FILE__;
        $line = !empty($exception) ? $exception->getLine() : __LINE__;
        $code = !empty($exception) ? $exception->getCode() : 0;
        $detailedMessageForLogs = !empty($exception) ? $exception->getMessage() : $message;
        $detailedMessageForLogs .= $file . ':' . $line;

        $isNotBalanceError = !str_contains($detailedMessageForLogs, 'Your balance is too low to proceed');
        $isNotPurchasedNumbersError = !str_contains($detailedMessageForLogs, 'has been purchased by someone else');

        // Raffle on LCS is used by many clients
        // It can happen when two people buy the same numbers in the same time
        // Only the first one will be bought and the second will receive below error response
        $isNotTakenNumbersError = !str_contains($detailedMessageForLogs, 'Failed to insert numbers into raffle closed pool. Most likely another request has already inserted');
        $shouldLogError = $code >= 500 && $isNotTakenNumbersError;
        $shouldLogInfo = $isNotBalanceError && $isNotPurchasedNumbersError && $isNotTakenNumbersError;
        if ($shouldLogError) {
            $fileLoggerService->error($detailedMessageForLogs);
        } else if ($shouldLogInfo) {
            $fileLoggerService->info($detailedMessageForLogs);
        }
    }

    FlashMessageHelper::set($type, $message);
    $redirect();
};

if (!Security::check_token()) {
    $error_handler(_('Your session has expired, please re-submit form.'), null, false);
}

if (Input::method() !== 'POST') {
    $redirect('/');
}
$user = Lotto_Settings::getInstance()->get('user');
$whitelabel = Lotto_Settings::getInstance()->get('whitelabel');

/** @var WhitelabelUser $user_dao */
$user_dao = Container::get(WhitelabelUser::class);

if (empty($user)) {
    /** @var WP_Query $wp_query */
    global $wp_query;
    $wp_query->set_404();
    status_header(403);
    get_template_part(403);
    exit();
}
$parent = get_post($post->post_parent);

// ---------------------- DEPENDENCIES ----------------------
$container = Container::forge();

/** @var Raffle $raffle_dao */
$raffle_dao = $container->get(Raffle::class);
/** @var Services_Raffle_Ticket $purchase_ticket_service */
$purchase_ticket_service = $container->get(Services_Raffle_Ticket::class);

// ---------------------- VARS ----------------------
$raffle = $raffle_dao->get_by_slug_with_currency_and_rule($parent->post_name);

$error = null;
try {
    $ticket = $purchase_ticket_service->purchase(
        $whitelabel['id'],
        $raffle->slug,
        'closed',
        $numbers,
        $user['id']
    );
} catch (Throwable $exception) {
    # used when Numbers validation fails (Services_Raffle_Number_Validator)
    if ($exception instanceof InvalidArgumentException) {
        $error_handler($exception->getMessage(), $exception);
    }
    # used when "Given numbers <%s> has been purchased by someone else. Please select new numbers." is thrown, only.
    if ($exception instanceof BadMethodCallException) {
        $error_handler($exception->getMessage(), $exception);
    }
    # guzzle client exception (catches when any exception is thrown from lcs).
    if ($exception instanceof ServerException && $exception->getCode() == 507) {
        $error_handler(_('Sorry, but some of your numbers have already been purchased. Please select a new ones and try again.'), $exception);
    }
    # unknown error
    if (Helpers_App::is_production_environment() || Helpers_App::is_staging_environment()) {
        $error_handler(_('Unknown error. Please contact us!'), $exception, true);
    }

    throw $exception;
}

# small hack to update user balance, because it is initialized in wordpress.php wrapper before execution is done
$user_orm = $user_dao->get_by_id($user['id']);
$user['balance'] = $user_orm->balance;
$user['bonus_balance'] = $user_orm->bonus_balance;
Lotto_Settings::getInstance()->set('user', $user);

try {
    /** @var MailerService $mailerService */
    $mailerService = Container::get(MailerService::class);
    $raffleMailer = new RaffleMailer($mailerService, $fileLoggerService);
    $raffleMailer->sendPurchaseEmail($ticket);
} catch (Throwable $exception) {
    $fileLoggerService->error(
        'Raffle ticket with token ' . $ticket->get_prefixed_token_attribute() . ' has been purchased, but we couldn\'t send confirmation email. ' .
        'Detailed message: ' . $exception->getMessage()
    );
}

include 'page-play-purchase.php';
