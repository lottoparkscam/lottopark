<?php

declare(strict_types=1);

namespace Repositories;

use Models\WhitelabelUserPromoCode;
use Repositories\Orm\AbstractRepository;
use Carbon\Carbon;
use Fuel\Core\Database_Query;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Exception;

class WhitelabelUserPromoCodeRepository extends AbstractRepository
{
    public function __construct(WhitelabelUserPromoCode $model)
    {
        parent::__construct($model);
    }

    /**
     * @throws Exception
     */
    public function savePromoCodeUsed(int $codeId, int $userId, ?int $type = null): void
    {
        $promoCode = new WhitelabelUserPromoCode();

        $promoCode->set([
            'whitelabel_promo_code_id' => $codeId,
            'whitelabel_user_id' => $userId,
            'type' => $type,
            'used_at' => Carbon::now(),
        ]);

        $promoCode->save();
    }

    /**
     * @throws Exception
     */
    public function setPromoCodeUsedForTransaction(int $transactionId, int $codeId, int $userId, int $type = null): void
    {
        /** @var Database_Query $query */

        $query = $this->db->update(WhitelabelUserPromoCode::get_table_name())
            ->set([
                'whitelabel_transaction_id' => $transactionId,
                'type' => $type,
                'used_at' => Carbon::now(),
            ])
            ->where('whitelabel_promo_code_id', $codeId)
            ->and_where('whitelabel_user_id', $userId);

        if ($query->execute() !== 1) {
            throw new Exception('Cannot update promo code ID: ' . $codeId);
        }
    }

    public function isCodeUsedByUser(int $codeId, int $userId): bool
    {
        return $this->recordExists(
            [
                new Model_Orm_Criteria_Where('whitelabel_promo_code_id', $codeId),
                new Model_Orm_Criteria_Where('whitelabel_user_id', $userId),
            ]
        );
    }

    public function findOneByCodeIdAndUserId(int $codeId, int $userId): ?WhitelabelUserPromoCode
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Where('whitelabel_promo_code_id', $codeId),
            new Model_Orm_Criteria_Where('whitelabel_user_id', $userId),
        ]);

        return $this->findOne();
    }
}