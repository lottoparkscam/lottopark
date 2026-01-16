<?php

namespace Modules\Payments;

use Enums\PaymentLogType;
use GuzzleHttp\Exception\ClientException;
use Lotto_Helper;
use Models\WhitelabelTransaction;
use Models\PaymentLog;
use Repositories\Orm\PaymentLogRepository;
use Services\Shared\Logger\LoggerContract;
use Throwable;

class PaymentLogger implements LoggerContract
{
    private PaymentLogRepository $repo;

    public function __construct(PaymentLogRepository $repo)
    {
        $this->repo = $repo;
    }

    public function logInfo(string $message, array $context = []): void
    {
        $log = $this->createFrom(PaymentLogType::INFO(), $message, $context);
        $this->repo->save($log, true);
    }

    public function logWarning(string $message, array $context = []): void
    {
        $log = $this->createFrom(PaymentLogType::WARNING(), $message, $context);
        $this->repo->save($log, true);
    }

    public function logError(string $message, array $context = []): void
    {
        $log = $this->createFrom(PaymentLogType::ERROR(), $message, $context);
        $this->repo->save($log, true);
    }

    public function logErrorFromException(Throwable $exception, array $context = []): void
    {
        $errorAsString = sprintf('%s, %s, %s', $exception->getMessage(), $exception->getFile(), $exception->getLine());
        $context['location'] = $errorAsString;
        $context['trace'] = $exception->getTraceAsString();

        if ($exception instanceof ClientException) {
            $context['response'] = json_decode($exception->getResponse()->getBody()->getContents(), true);
        }

        $log = $this->createFrom(PaymentLogType::ERROR(), $exception->getMessage(), $context);
        $this->repo->save($log, true);
    }

    public function logSuccess(string $message, array $context = []): void
    {
        $log = $this->createFrom(PaymentLogType::SUCCESS(), $message, $context);
        $this->repo->save($log, true);
    }

    private function createFrom(PaymentLogType $type, string $message, array $context = []): PaymentLog
    {
        if (empty($context['transaction'])) {
            return PaymentLog::createFromData(
                $context,
                $type,
                $message,
                $context
            );
        }
        /** @var WhitelabelTransaction $transaction */
        $transaction = $context['transaction'];
        $context['transaction'] = $this->getDataFromTransaction($transaction);

        return PaymentLog::createFromTransaction(
            $transaction,
            $type,
            $message,
            $context
        );
    }

    private function getDataFromTransaction(WhitelabelTransaction $transaction): array
    {
        $data = $transaction->to_array();

        if (isset($data['user'])) {
            $data['user_login'] = $data['user']['email'];
            $data['user_email'] = $data['user']['login'];
            $data['user_token'] = Lotto_Helper::get_user_token($data['user'], $data['whitelabel']);
            unset($data['user']);
        }

        if (isset($data['whitelabel_user'])) {
            $data['user_login'] = $data['whitelabel_user']['email'];
            $data['user_email'] = $data['whitelabel_user']['login'];
            $data['user_token'] = Lotto_Helper::get_user_token($data['whitelabel_user'], $data['whitelabel']);
            unset($data['whitelabel_user']);
        }

        if (isset($data['whitelabel'])) {
            $data['whitelabel_name'] = $data['whitelabel']['name'];
            unset($data['whitelabel']);
        }

        return $data;
    }
}
