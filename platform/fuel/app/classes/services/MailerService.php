<?php

namespace Services;

use Email\Email_Driver;
use Helpers_General;
use Lotto_Helper;
use Services\Logs\FileLoggerService;
use Throwable;

class MailerService
{
    private Email_Driver $mailer;
    private FileLoggerService $fileLoggerService;

    public function __construct(FileLoggerService $fileLoggerService, Email_Driver $mailer)
    {
        $this->fileLoggerService = $fileLoggerService;
        $this->mailer = $mailer;
    }

    /**
     * The same as send but auto generate from for whitelabelName and domain.
     * By public we understand non internal mails, e.g. public mail is an email sent to user on their private inbox.
     * @see send
     */
    public function sendPublic($to, string $title, string $body, string $whitelabelName, bool $isBodyHtml = false): bool
    {
        $from = [
            'email' => 'noreply+' . time() . '@' . Lotto_Helper::getWhitelabelDomainFromUrl(),
            'name' => $whitelabelName
        ];
        return $this->send($to, $title, $body, $from, $isBodyHtml);
    }

    /**
     * @param array|string $to
     * @param string $title
     * @param string $body
     * @param array $from IMPORTANT: if you use this method for public emails pay attention to this variable. Most likely default will be insufficient.
     * @return bool
     */
    public function send(array|string $to, string $title, string $body, array $from = [], bool $isBodyHtml = false, array $replyTo = []): bool
    {
        if (!empty($from)) {
            ['email' => $email, 'name' => $name] = $from;
            $this->mailer->from($email, $name);
        } else {
            $this->mailer->from('noreply@' . Helpers_General::get_domain(), 'WhiteLotto');
        }

        if (!empty($replyTo)) {
            ['email' => $email, 'name' => $name] = $replyTo;
            $this->mailer->reply_to($email, $name);
        } else {
            $this->mailer->reply_to('noreply@' . Helpers_General::get_domain(), 'WhiteLotto');
        }

        $this->mailer->to($to);
        $this->mailer->subject($title);

        if ($isBodyHtml) {
            $this->mailer->html_body($body);
        } else {
            $this->mailer->body($body);
        }

        try {
            return $this->mailer->send();
        } catch (Throwable $e) {
            $errorMessage = "There is a problem with delivering the mail. " .
                "Description of error: " . $e->getMessage();
            $this->fileLoggerService->error(
                $errorMessage
            );

            return false;
        }
    }
}
