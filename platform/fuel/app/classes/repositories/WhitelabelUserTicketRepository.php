<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\WhitelabelUserTicket;
use Repositories\Orm\AbstractRepository;
use Services\Logs\FileLoggerService;
use Throwable;
use Wrappers\Db;

/**
 * @method WhitelabelUserTicket findOneById(int $ticketId)
 */
class WhitelabelUserTicketRepository extends AbstractRepository
{
    private FileLoggerService $fileLoggerService;

    public function __construct(WhitelabelUserTicket $model, Db $db)
    {
        parent::__construct($model);
        $this->db = $db;
    }

    public function getOneByTokenAndWhitelabelId(int $ticketToken, int $ticketWhitelabelId): ?WhitelabelUserTicket
    {
        /**@var WhitelabelUserTicket $ModelOrmWhitelabelUserTicket */
        $ModelOrmWhitelabelUserTicket = $this->pushCriterias([
            new Model_Orm_Criteria_Where('token', $ticketToken),
            new Model_Orm_Criteria_Where('whitelabel_id', $ticketWhitelabelId),
        ])->getOne();
        return $ModelOrmWhitelabelUserTicket;
    }

    public function getOneByUserAndLotteryId(int $userId, int $lotteryId): ?WhitelabelUserTicket
    {
        /**@var WhitelabelUserTicket $ModelOrmWhitelabelUserTicket */

        $ModelOrmWhitelabelUserTicket = $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_user_id', $userId),
            new Model_Orm_Criteria_Where('lottery_id', $lotteryId),
        ])->getOne();

        return $ModelOrmWhitelabelUserTicket;
    }

    public function changeIsLtechInsufficientBalance(array $ticketIds, bool $isInsufficient): void
    {
        if (empty($ticketIds)) {
            return;
        }

        $this->db->update($this->model->get_table_name())
            ->set([
                'is_ltech_insufficient_balance' => $isInsufficient
            ])
            ->where('id', 'IN', $ticketIds)
            ->execute();
    }

    public function setTicketsAsInsufficientByCurrencyId(array $currenciesIds): void
    {
        if (empty($currenciesIds)) {
            return;
        }

        $currenciesIds = implode(', ', $currenciesIds);
        $idsToUpdateQuery = "SELECT 
            DISTINCT wut.id AS id
        FROM whitelabel_user_ticket_line wutl
        INNER JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
        INNER JOIN lottery_provider lp ON lp.id = wut.lottery_provider_id
        INNER JOIN lottery lot ON lot.id = wut.lottery_id
        INNER JOIN currency c ON lot.currency_id = c.id
        INNER JOIN whitelabel_lottery wl ON wut.whitelabel_id = wl.whitelabel_id AND wut.lottery_id = wl.lottery_id
        LEFT JOIN lottorisq_ticket lt ON lt.whitelabel_user_ticket_slip_id = wutl.whitelabel_user_ticket_slip_id
        WHERE wutl.whitelabel_user_ticket_slip_id IS NOT NULL
            AND wut.paid = 1 
            AND wut.status = 0 
            AND wut.date_processed IS NULL
            AND provider = 1 
            AND wut.model != 3
            AND lt.id IS NULL 
            AND lot.next_date_local >= wut.draw_date
            AND wut.whitelabel_id != 20
            AND wut.is_ltech_insufficient_balance = 0
            AND c.id IN ($currenciesIds)";

        try {
            /** @var mixed $idsToUpdate */
            $idsToUpdate = $this->db->query($idsToUpdateQuery)->execute();
            $idsToUpdate = $idsToUpdate->as_array();
            $idsToUpdate = array_column($idsToUpdate, 'id');
            $idsToUpdateInString = implode(', ', $idsToUpdate);
            if (!empty($idsToUpdateInString)) {
                $updateQuery = "UPDATE whitelabel_user_ticket SET is_ltech_insufficient_balance = 1 WHERE id IN ($idsToUpdateInString)";
                $this->db->query($updateQuery)->execute();
            }
        } catch (Throwable $exception) {
            $this->fileLoggerService->error(
                "Cannot update tickets (set is_ltech_insufficient_balance): " . $exception->getMessage()
            );
        }
    }

    public function getLotterySlugByTicketId(int $ticketId, int $whitelabelId): string
    {
        /** @var mixed $query */
        $query = $this->model::query()
            ->related('lottery', [
                'select' => ['slug']
            ])
            ->where('id', $ticketId)
            ->where('whitelabel_id', $whitelabelId)
            ->get_one();

        return $query ? $query->lottery->slug : '';
    }
}
