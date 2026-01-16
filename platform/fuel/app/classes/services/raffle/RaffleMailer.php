<?php

use Models\Raffle;
use Models\Whitelabel;
use Services\MailerService;
use Models\WhitelabelRaffleTicket;
use Forms\Wordpress\Forms_Wordpress_Email;
use Services\Logs\FileLoggerService;

final class RaffleMailer
{
    public const RAFFLE_BUY_SLUG = 'raffle-buy';
    private MailerService $mailerService;
    private FileLoggerService $fileLoggerService;

    public function __construct(MailerService $mailerService, FileLoggerService $fileLoggerService)
    {
        $this->mailerService = $mailerService;
        $this->fileLoggerService = $fileLoggerService;
    }

    public function sendPurchaseEmail(WhitelabelRaffleTicket $ticket): bool
    {
        try {
            $emailData = $this->generatePurchaseEmail($ticket);
        } catch (Throwable $exception) {
            $this->fileLoggerService->error(
                'There is a problem with generating raffle purchase email content. ' .
                'The ticket with token ' . $ticket->get_prefixed_token_attribute() . ' might not have required data, detailed message: ' . $exception->getMessage()
            );
            return false;
        }

        /** @var Whitelabel $whitelabel */
        $whitelabel = $ticket->whitelabel;
        $isEmailSent = $this->sendEmail($ticket->user['email'], $emailData['title'], $emailData['body_html'], $whitelabel->name);
        return $isEmailSent;
    }

    public function generatePurchaseEmail(WhitelabelRaffleTicket $ticket): array
    {
        /** @var Raffle $raffle */
        $raffle = $ticket->raffle;
        $language = $ticket->user->language->code;
        $emailData = [
            'amount' => $ticket->amount,
            'currency' => $ticket->currency->code,
            'tickets' => [$ticket->to_array()],
            'raffleName' => $raffle->name,
            'purchaseDate' => $ticket->transaction ? $ticket->transaction->date : $ticket->createdAt,
            'drawDate' => $ticket->draw_date ?? '',
        ];

        $emailGenerator = new Forms_Wordpress_Email($ticket->whitelabel->to_array());
        return $emailGenerator->get_email(RaffleMailer::RAFFLE_BUY_SLUG, $language, $emailData);
    }

    private function sendEmail(string $to, string $title, string $bodyHtml, string $whitelabelName): bool
    {
        return $this->mailerService->sendPublic($to, $title, $bodyHtml, $whitelabelName, true);
    }
}