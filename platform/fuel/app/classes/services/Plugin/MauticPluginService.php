<?php

namespace Services\Plugin;

use Model_Whitelabel_Transaction;
use Repositories\Orm\WhitelabelPaymentMethodRepository;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\WhitelabelTransactionRepository;
use Models\WhitelabelPaymentMethod;
use Models\WhitelabelTransaction;
use Models\WhitelabelUser;
use Helpers_General;
use Services\Logs\FileLoggerService;
use Services\TransactionService;
use Services_Api_Mautic;
use InvalidArgumentException;
use Exception;

class MauticPluginService extends PluginService
{
    use Services_Api_Mautic;

    private ?WhitelabelPaymentMethod $whitelabelPaymentMethod = null;

    private WhitelabelTransactionRepository $whitelabelTransactionRepository;
    private WhitelabelPaymentMethodRepository $whitelabelPaymentMethodRepository;
    private TransactionService $transactionService;

    public function __construct(
        WhitelabelUserRepository $whitelabelUserRepository,
        WhitelabelTransactionRepository $whitelabelTransactionRepository,
        WhitelabelPaymentMethodRepository $whitelabelPaymentMethodRepository,
        TransactionService $transactionService,
        FileLoggerService $fileLoggerService,
    ) {
        parent::__construct($whitelabelUserRepository, $fileLoggerService);

        $this->whitelabelTransactionRepository = $whitelabelTransactionRepository;
        $this->whitelabelPaymentMethodRepository = $whitelabelPaymentMethodRepository;
        $this->transactionService = $transactionService;
    }

    public function setWhitelabelPaymentMethod(int $paymentMethodId): void
    {
        $this->whitelabelPaymentMethod = $this->whitelabelPaymentMethodRepository->findOneById($paymentMethodId);
    }

    public function createDeposit(int $transactionId): array
    {
        try {
            $whitelabelUser = $this->getWhitelabelUser();

            if ($whitelabelUser === null) {
                throw new Exception('WhitelabelUser is not set.');
            }

            /** @var Model_Whitelabel_Transaction $whitelabelTransaction */

            /**
             * Using WhitelabelTransaction model or repository with 'findOne' method instead:
             * ```$this->whitelabelTransactionRepository->findOneById($transactionId);```
             * results in the loss of payment ID information further down in the payments forms,
             * where the old and new model are alternately used. This is due to the model cache.
             */
            $whitelabelTransaction = Model_Whitelabel_Transaction::find_by_pk($transactionId);

            if ($whitelabelTransaction === null) {
                throw new Exception('WhitelabelTransaction is not set.');
            }

            if ((int)$whitelabelTransaction['type'] !== Helpers_General::TYPE_TRANSACTION_DEPOSIT) {
                throw new InvalidArgumentException(sprintf(
                    "Invalid WhitelabelTransaction type '%s' for transaction ID: %s.",
                    $this->transactionService->getTransactionType(new WhitelabelTransaction($whitelabelTransaction->to_array())),
                    $whitelabelTransaction['id']
                ));
            }

            if ($this->whitelabelPaymentMethod === null && !empty($whitelabelTransaction['whitelabel_payment_method_id'])) {
                $this->setWhitelabelPaymentMethod($whitelabelTransaction['whitelabel_payment_method_id']);
            }

            return $this->getTransactionDeposit(
                $whitelabelUser,
                $whitelabelTransaction,
                $this->whitelabelPaymentMethod
            );

        } catch (Exception $exception) {
            $this->fileLoggerService->error(
                $exception->getMessage()
            );
        }

        return [];
    }

    public function getTransactionDeposit(
        WhitelabelUser $whitelabelUser,
        Model_Whitelabel_Transaction $whitelabelTransaction,
        ?WhitelabelPaymentMethod $paymentMethod
    ): array {

        $userFirstDeposit = $whitelabelUser->first_deposit_amount_manager == null;

        return $this->getMauticTransactionDeposit(
            $userFirstDeposit,
            $whitelabelTransaction['amount_manager'],
            $this->transactionService->getTransactionStatus(new WhitelabelTransaction($whitelabelTransaction->to_array())),
            (bool)$whitelabelTransaction['is_casino'],
            $whitelabelUser->total_deposit_manager,
            $paymentMethod?->name,
        );
    }
}