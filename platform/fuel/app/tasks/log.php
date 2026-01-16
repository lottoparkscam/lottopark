<?php

namespace Fuel\Tasks;

use Carbon\Carbon;
use Container;
use Fuel\Core\Arr;
use Helpers\ArrayHelper;
use Helpers_General;
use Helpers_Time;
use Models\PaymentMethod;
use Models\Raffle;
use Models\WhitelabelRaffle;
use Models\RaffleLog;
use Services\MailerService;
use Models\LottorisqLog;
use Models\PaymentLog;
use Services\SlackService;
use Task_Cli;
use Wrappers\Db;
use Wrappers\Decorators\ConfigContract;

final class Log extends Task_Cli
{
    private SlackService $slackService;
    private Db $db;
    private MailerService $mailer;

    public function __construct()
    {
        $this->config = Container::get(ConfigContract::class);
        $this->slackService = Container::get(SlackService::class);
        $this->db = Container::get(Db::class);
        $this->mailer = Container::get(MailerService::class);
    }

    public function checkErrorsPaymentLog(): void
    {
        $this->checkSuccessAfterError(PaymentLog::class, PaymentMethod::class);
    }

    public function checkErrorsRaffleLog(): void
    {
        $this->checkSuccessAfterError(RaffleLog::class, Raffle::class);
    }

    private function checkSuccessAfterError(string $modelClass, string $groupBy)
    {
        $groupByTableName = $groupBy::get_table_name();
        $groupByFieldName = "{$groupByTableName}_id";

        $beforeThisDateAllErrorsWasFixed = '2021-03-11 8:46:00';
        $tableName = $modelClass::get_table_name();
        $typeError = Helpers_General::TYPE_ERROR;
        $logsIds = $this->db->query(
            "SELECT MAX(id) as id, MAX(date) as date FROM $tableName
            WHERE type = $typeError
            AND date > '$beforeThisDateAllErrorsWasFixed'
            GROUP BY $groupByFieldName
            ORDER BY date DESC
            LIMIT 100"
        )->execute()->as_array();
        $logsIds = ArrayHelper::createSingleArrayFromValue($logsIds, 'id');

        $logs = !empty($logsIds) ? $modelClass::find('all', [
            'where' => [
                ['id', 'IN', $logsIds]
            ]
        ]) : [];

        switch ($modelClass) {
            case PaymentLog::class:
                $logs = array_filter($logs, fn ($log) => $log->whitelabelPaymentMethod->show ?? false);
                break;
            case RaffleLog::class:
                $logs = array_filter($logs, fn ($log) => $log->raffle->isEnabled ?? false);
                break;
        }

        $haveBeenAlreadyLogged = [];

        $errorsSummary = "Rows from $modelClass without success log after error log:\n";
        $errors = "Rows from $modelClass without success log after error log:\n";
        $errorsCount = 0;

        foreach ($logs as $log) {
            if (in_array($log->$groupByFieldName, $haveBeenAlreadyLogged)) {
                continue;
            }

            $date = $log->date->format(Helpers_Time::DATETIME_FORMAT);
            $dayAgo = (Carbon::now())->subDay()->format(Helpers_Time::DATETIME_FORMAT);
            $isToEarlyToCheck = $date > $dayAgo;
            if ($isToEarlyToCheck) {
                continue;
            }

            $isSuccessAfterError = !empty($modelClass::find('first', [
                'where' => [
                    'type' => Helpers_General::TYPE_SUCCESS,
                    ['date', '>=', $date],
                    $groupByFieldName => $log->$groupByFieldName
                ],
                'order_by' => [
                    'id' => 'DESC'
                ]
            ]));

            $messageIsTimeOutType = str_contains($log->message, "Your request timed out");
            $isTryingToPayAgain = str_contains($log->message, "Attempted to pay already approved transaction");
            $groupByDoesNotExist = empty($log->$groupByFieldName);
            if ($isSuccessAfterError || $messageIsTimeOutType || $groupByDoesNotExist || $isTryingToPayAgain) {
                continue;
            }

            $lastSuccess = $modelClass::find('first', [
                'where' => [
                    'type' => Helpers_General::TYPE_SUCCESS,
                    $groupByFieldName => $log->$groupByFieldName
                ],
                'order_by' => [
                    'date' => 'DESC'
                ]
            ]);

            if (!empty($lastSuccess->date)) {
                $lastSuccessDate = $lastSuccess->date->format(Helpers_Time::DATETIME_FORMAT);
                $howManyErrorsOccurredFromSuccess = count($modelClass::find('all', [
                    'where' => [
                        'type' => Helpers_General::TYPE_ERROR,
                        $groupByFieldName => $log->$groupByFieldName,
                        ['date', '>=', $lastSuccessDate]
                    ]
                ]));
            } else {
                $lastSuccessDate = null;
                $howManyErrorsOccurredFromSuccess = count($modelClass::find('all', [
                    'where' => [
                        'type' => Helpers_General::TYPE_ERROR,
                        $groupByFieldName => $log->$groupByFieldName
                    ]
                ]));
            }

            $tooFewErrors = $howManyErrorsOccurredFromSuccess < 5;
            if ($tooFewErrors) {
                continue;
            }

            ++$errorsCount;
            $shouldGetGroupName = $modelClass !== LottorisqLog::class && !empty($log->$groupByTableName);
            $name = $shouldGetGroupName ? ", name: {$log->$groupByTableName->name}" : '';
            $message = htmlspecialchars($log->message, ENT_QUOTES);
            $lastSuccessDateDisplay = $lastSuccessDate ?? 'none';
            $errorsSummary .= "[id: {$log->id}, $groupByFieldName: [id: {$log->$groupByFieldName}{$name}], last_error: $date, last_success: $lastSuccessDateDisplay]\n";
            $errors .= "[id: {$log->id}, \n $groupByFieldName: [id: {$log->$groupByFieldName}{$name}], \n last_error: $date, \n last_success: $lastSuccessDateDisplay, \n message: $message] \n";
            $haveBeenAlreadyLogged[] = $log->$groupByFieldName;
        }

        $infoChannelEmail = "info-team-dev-whitelo-aaaac7cg3viehzi7c6byweefgu@ggintsoftware.slack.com";

        if ($errorsCount > 0) {
            $this->slackService->warning($errorsSummary);
            $this->mailer->send($infoChannelEmail, "Errors from $modelClass", $errors);
            return;
        }

        switch ($modelClass) {
            case PaymentLog::class:
                $anyLogNotExistsInLongPeriod = $this->checkIfLogsNotExist(
                    PaymentMethod::class,
                    null,
                    null,
                    [],
                    PaymentLog::class,
                    5
                );

                if ($anyLogNotExistsInLongPeriod) {
                    return;
                }

                break;
            case RaffleLog::class:
                $anyLogNotExists = $this->checkIfLogsNotExist(
                    WhitelabelRaffle::class,
                    'raffle_id',
                    'raffle',
                    [
                        'where' => [
                            'is_enabled' => true
                        ],
                        'related' => [
                            'raffle' => [
                                'where' => [
                                    'is_enabled' => true
                                ]
                            ]
                        ]
                    ],
                    RaffleLog::class,
                    20
                );

                if ($anyLogNotExists) {
                    return;
                }

                break;
        }

        $errorsSummary = "There are no permanent errors from $modelClass! Nice work!";
        $this->slackService->info($errorsSummary);
    }

    private function checkIfLogsNotExist(
        string $groupByClass,
        ?string $commonRelationName,
        ?string $nameFrom,
        array $conditions,
        string $logClass,
        int $days
    ): bool {
        $fewDaysAgoFormatted = Carbon::now()->subDays($days)->format(Helpers_Time::DATETIME_FORMAT);
        $names = [];

        if ($logClass === PaymentLog::class) {
            $shownPaymentMethods = $this->db->select(['payment_method_id', 'id'])
                ->from('whitelabel_payment_method')
                ->where('show', '=', true)
                ->group_by('payment_method_id')
                ->execute()
                ->as_array();
            $idsShownPaymentMethods = Arr::pluck($shownPaymentMethods, 'id');

            if (empty($idsShownPaymentMethods)) {
                return false;
            }

            $filteredModels = $groupByClass::find('all', [
                'where' => [
                    ['id', 'IN', $idsShownPaymentMethods],
                ]
            ]);
        } else {
            $filteredModels = $groupByClass::find('all', $conditions);
        }

        $fieldName = $commonRelationName;
        if (empty($commonRelationName)) {
            $groupByTableName = $groupByClass::get_table_name();
            $fieldName = "{$groupByTableName}_id";
            $commonRelationName = 'id';
        }

        foreach ($filteredModels as $model) {
            $logs = $logClass::find('all', [
                'where' => [
                    $fieldName => $model->$commonRelationName,
                    ['date', '>', $fewDaysAgoFormatted]
                ]
            ]);

            $anyLogExists = empty($logs);

            if (empty($nameFrom)) {
                $name = $model->name;
            } else {
                $name = $model->$nameFrom->name;
            }

            if ($anyLogExists && !in_array($name, $names)) {
                $names[] = $name;
            }
        }

        if (!empty($names)) {
            $namesFormatted = implode(', ', $names);

            // Luminaria Raffle has large pool size
            // It can take much longer than 20 days till whole pool will be bought
            $isNotLuminariaRaffle = !($nameFrom = 'raffle' && $namesFormatted = 'Luminaria Raffle');
            if ($isNotLuminariaRaffle) {
                $this->slackService->warning("Any log from $logClass since $days days ago has not shown where $nameFrom is: $namesFormatted");
            }
        }

        return !empty($names);
    }
}
