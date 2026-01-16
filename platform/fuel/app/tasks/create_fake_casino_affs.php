<?php

namespace Fuel\Tasks;

use Fuel\Core\DB;
use Models\WhitelabelAff;
use Fuel\Tasks\Factory\Utils\Faker;
use Models\SlotTransaction;
use Models\WhitelabelAffGroup;
use Models\WhitelabelUser;
use Models\WhitelabelUserAff;

final class Create_Fake_Casino_Affs
{
    private function createWhitelabelAffGroup(): WhitelabelAffGroup
    {
        $group = new WhitelabelAffGroup();
        $group->whitelabelId = 1;
        $group->name = 'Testing';
        $group->casinoCommissionValueManager = 25;
        $group->casinoCommissionValue_2Manager = 5;
        $group->save();

        return $group;
    }

    private function createWhitelabelAffUser(WhitelabelAff $whitelabelAff, WhitelabelUser $whitelabelUser): WhitelabelUserAff
    {
        $affUser = new WhitelabelUserAff();
        $affUser->whitelabelId = 1;
        $affUser->whitelabelAffId = $whitelabelAff->id;
        $affUser->whitelabelUserId = $whitelabelUser->id;
        $affUser->isAccepted = 1;
        $affUser->isDeleted = 0;
        $affUser->isExpired = 0;
        $affUser->isCasino = rand(0, 1) === 1;

        $affUser->save();

        return $affUser;
    }

    private function createWhitelabelUser(): WhitelabelUser
    {
        $user = new WhitelabelUser();

        $user->whitelabelId = 1;
        $user->currencyId = 1;
        $user->activation_hash = null;
        $user->activation_valid = null;
        $user->address1 = Faker::forge()->address();
        $user->address2 = Faker::forge()->streetAddress();
        $user->balance = Faker::forge()->numberBetween(1, 10);
        $user->bonus_balance = Faker::forge()->numberBetween(1, 10);
        $user->birthdate = null;
        $user->browser_type = null;
        $user->city = Faker::forge()->city();
        $user->country = Faker::forge()->countryCode();
        $user->date_register = Faker::forge()->date();
        $user->email = Faker::forge()->email();
        $user->first_deposit = null;
        $user->first_deposit_amount_manager = null;
        $user->first_purchase = null;
        $user->gender = Faker::forge()->randomElement([0, 1, 2]);
        $user->is_active = Faker::forge()->boolean(80);
        $user->hash = Faker::forge()->uuid();
        $user->is_confirmed = Faker::forge()->boolean(70);
        $user->is_deleted = 0;
        $user->date_delete = null;
        $user->language_id = 1;
        $user->last_active = Faker::forge()->date();
        $user->last_country = null;
        $user->last_deposit_amount_manager = null;
        $user->last_deposit_date = null;
        $user->last_ip = Faker::forge()->ipv4();
        $user->last_purchase_amount_manager = null;
        $user->last_purchase_date = null;
        $user->last_update = Faker::forge()->date();
        $user->login = substr(Faker::forge()->name(), 0, 18) . Faker::forge()->numberBetween(0, 99);
        $user->lines_sold_quantity = 0;
        $user->lost_hash = null;
        $user->national_id = Faker::forge()->numerify('ABC###');
        $user->lost_last = null;
        $user->name = Faker::forge()->name();
        $user->net_winnings_manager = null;
        $user->phone = Faker::forge()->phoneNumber();
        $user->phone_country = Faker::forge()->countryCode();
        $user->pnl_manager = null;
        $user->refer_bonus_used = Faker::forge()->boolean(20);
        $user->register_country = Faker::forge()->countryCode();
        $user->register_ip = Faker::forge()->ipv4();
        $user->resend_hash = null;
        $user->sale_status = 0;
        $user->salt = Faker::forge()->uuid();
        $user->second_deposit = null;
        $user->second_purchase = null;
        $user->sent_welcome_mail = Faker::forge()->boolean();
        $user->state = Faker::forge()->state();
        $user->surname = Faker::forge()->firstNameMale();
        $user->system_type = null;
        $user->tickets_sold_quantity = 0;
        $user->timezone = Faker::forge()->timezone();
        $user->token = Faker::forge()->numberBetween(10000, 9999999);
        $user->total_deposit_manager = null;
        $user->total_net_income_manager = null;
        $user->total_purchases_manager = null;
        $user->total_withdrawal_manager = null;
        $user->zip = Faker::forge()->numberBetween(1000, 999999);

        $user->save();

        return $user;
    }

    private function createWhitelabelAff(?int $whitelabelAffGroupId = null, ?int $whitelabelAffParentId = null): WhitelabelAff
    {
        $aff = new WhitelabelAff();
        $aff->whitelabelId = 1;
        $aff->languageId = 1;
        $aff->currencyId = 1;

        if (!is_null($whitelabelAffGroupId)) {
            $aff->whitelabelAffGroupId = $whitelabelAffGroupId;
        }

        if (!is_null($whitelabelAffParentId)) {
            $aff->whitelabelAffParentId = $whitelabelAffParentId;
        }

        $aff->isAccepted = 1;
        $aff->isConfirmed = 1;
        $aff->isActive = 1;
        $aff->login = substr(Faker::forge()->uuid(), 0, 20);
        $aff->email = Faker::forge()->email();
        $aff->isDeleted = 0;
        $aff->token = Faker::forge()->numberBetween(10000, 9999999);
        $aff->subAffiliateToken = substr(Faker::forge()->uuid(), 0, 10);
        $aff->hash = Faker::forge()->uuid();
        $aff->salt = Faker::forge()->uuid();
        $aff->address1 = Faker::forge()->address();
        $aff->address2 = Faker::forge()->address();
        $aff->city = Faker::forge()->city();
        $aff->country = 'PL';
        $aff->state = 'PL';
        $aff->zip = '00-222';
        $aff->phoneCountry = 'PL';
        $aff->phone = Faker::forge()->phoneNumber();
        $aff->timezone = '';
        $aff->affLeadLifetime = 0;
        $aff->dateCreated = Faker::forge()->date();
        $aff->isShowName = 0;
        $aff->hideLeadId = 0;
        $aff->hideTransactionId = 0;
        $aff->save();

        return $aff;
    }

    private function createSlotTransaction(WhitelabelUser $whitelabelUser, string $action): SlotTransaction
    {
        DB::query("SET foreign_key_checks = 0;")->execute();

        $amount = Faker::forge()->numberBetween(2, 20);
        $slotTransaction = new SlotTransaction();
        $slotTransaction->slotGameId = 1;
        $slotTransaction->slotOpenGameId = 1;
        $slotTransaction->currencyId = 1;
        $slotTransaction->whitelabelUserId = $whitelabelUser->id;
        $slotTransaction->whitelabelSlotProviderId = 1;
        $slotTransaction->providerTransactionId = substr(Faker::forge()->uuid(), 0, 20);
        $slotTransaction->amount = $amount;
        $slotTransaction->amountManager = $amount;
        $slotTransaction->amountUsd = $amount;
        $slotTransaction->createdAt = Faker::forge()->date();
        $slotTransaction->token = Faker::forge()->numberBetween(9999, 99999999999999);
        $slotTransaction->action = $action;
        $slotTransaction->type = $action;
        $slotTransaction->additionalData = json_encode(['x']);
        $slotTransaction->save();
        DB::query("SET foreign_key_checks = 1;")->execute();

        return $slotTransaction;
    }

    public function run()
    {
        ini_set('max_execution_time', '300');
        set_time_limit(300);

        $group = $this->createWhitelabelAffGroup();

        $affCount = 500;
        $commissionPerAffCount = 2;

        for ($i = 0; $i < $affCount; $i++) {
            $aff = $this->createWhitelabelAff($group->id);
            $subAff = $this->createWhitelabelAff(null, $aff->id);
            $affOrSub = rand(0, 1);

            $whitelabelUser = $this->createWhitelabelUser();
            $userAff = $this->createWhitelabelAffUser($affOrSub === 0 ? $aff : $subAff, $whitelabelUser);

            if ($userAff->isCasino == true) {
                for ($j = 0; $j < $commissionPerAffCount; $j++) {
                    $this->createSlotTransaction($whitelabelUser, rand(0, 1) ? 'bet' : 'win');
                }
            }
        }

        $skipDate = true;
        $task = new Update_Casino_Commission_For_Affs($skipDate);
        $task->run();
    }
}
