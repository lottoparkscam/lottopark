<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Group;
use Classes\Orm\Criteria\Model_Orm_Criteria_Select;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\WhitelabelUser;
use Models\PaymentMethod;
use Repositories\Orm\AbstractRepository;
use Models\PaymentRequestLock;

class PaymentRequestLockRepository  extends AbstractRepository
{
    public function __construct(PaymentRequestLock $model)
    {
        parent::__construct($model);
    }

	public function findOneByUserAndPaymentMethod(WhitelabelUser $whitelabelUser, PaymentMethod $paymentMethod): ?PaymentRequestLock
	{
		return $this->pushCriterias([
			new Model_Orm_Criteria_Where('whitelabel_user_id', $whitelabelUser->id),
			new Model_Orm_Criteria_Where('payment_method_id', $paymentMethod->id),
		])->findOne();
	}

	/**
	 *
	 * @param WhitelabelUser $whitelabelUser
	 * @param integer $showCaptchaAfterNthTry
	 * @return boolean
	 */
	public function shouldDisplayCaptcha(WhitelabelUser $whitelabelUser, int $showCaptchaAfterNthTry): bool
	{
		/** 
		* @var float $requestsSum		 * 
		 */
		$requestsSum = (int)$this->db->select([
			$this->db->expr('SUM(requests_count)'), 'requests_sum'
		])
			->from($this->model::get_table_name())
			->where('whitelabel_user_id', $whitelabelUser->id)
			->execute()
			->as_array()[0]['requests_sum'] ?? 0;

		return $requestsSum !== 0 && $requestsSum % $showCaptchaAfterNthTry === 0;
	}
}