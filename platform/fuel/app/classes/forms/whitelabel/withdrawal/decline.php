<?php

use Wrappers\Db;
use Models\WhitelabelUser;
use Modules\Account\Balance\CasinoBalance;
use Modules\Account\Balance\RegularBalance;
use Repositories\Orm\WhitelabelUserRepository;

/**
 * Description of Forms_Whitelabel_Withdrawal_Decline
 */
final class Forms_Whitelabel_Withdrawal_Decline extends Forms_Main
{
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @var string|null
     */
    private $token = null;
    private RegularBalance $regularBalance;
    private CasinoBalance $casinoBalance;
    private Db $db;
    private WhitelabelUserRepository $whitelabelUserRepository;
    
    /**
     *
     * @param string $token
     * @param array $whitelabel
     */
    public function __construct(string $token = null, array $whitelabel = [])
    {
        $this->token = $token;
        $this->whitelabel = $whitelabel;
        $this->regularBalance = Container::get(RegularBalance::class);
        $this->casinoBalance = Container::get(CasinoBalance::class);
        $this->db = Container::get(Db::class);
        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }

    /**
     *
     * @return string|null
     */
    public function get_token():? string
    {
        return $this->token;
    }
    
    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        $whitelabel = $this->get_whitelabel();
        $token = $this->get_token();
        
        if (empty($whitelabel) || empty($token)) {
            return self::RESULT_INCORRECT_WITHDRAWAL;
        }
        
        $withdrawal = Model_Withdrawal_Request::find_by([
            "whitelabel_id" => $whitelabel['id'],
            "token" => $token
        ]);
        
        if (!empty($withdrawal) &&
            (int)$withdrawal[0]->whitelabel_id === (int)$whitelabel['id'] &&
            (int)$withdrawal[0]->status === Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_PENDING
        ) {
            $withdrawal = $withdrawal[0];
            $withdrawalDeclined = $this->db->inTransaction(function() use ($withdrawal) {
                $withdrawal->set([
                    'status' => Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_DECLINED
                ]);
                $withdrawal->save();
                /** @var WhitelabelUser $user */
                $user = WhitelabelUser::find($withdrawal['whitelabel_user_id']);


                $isCasino = (bool)$withdrawal['is_casino'];
                if ($isCasino) {
                    $this->casinoBalance->increase(
                        $user->id,
                        $withdrawal['amount'],
                        $user->currency->code
                    );
                    $this->casinoBalance->dispatch();
                } else {
                    $this->regularBalance->increase(
                        $user->id,
                        $withdrawal['amount'],
                        $user->currency->code
                    );
                    $this->regularBalance->dispatch();
                }
                $this->whitelabelUserRepository->updateFloatField(
                    $user->id,
                    'total_withdrawal_manager',
                    -(float)$withdrawal['amount_manager']
                );
            });
        } else {
            return self::RESULT_INCORRECT_WITHDRAWAL;
        }

        if (empty($withdrawalDeclined)) {
            return self::RESULT_INCORRECT_WITHDRAWAL;
        }
        
        return self::RESULT_OK;
    }
}
