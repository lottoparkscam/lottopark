<?php

use Models\Raffle;

/**
 * Checks given raffle is enabled
 */
class Services_Raffle_Status_Verifier
{
    private Raffle $raffle_dao;

    /** @var Raffle[] */
    private array $cached_raffle = [];

    public function __construct(Raffle $raffle)
    {
        $this->raffle_dao = $raffle;
    }

    public function getAndVerifyPlayableRaffle(string $raffle_slug): Raffle
    {
        $raffle = $this->get_and_verify_raffle($raffle_slug);

        if ($raffle->is_turned_on === false) {
            throw new RuntimeException(sprintf('Raffle <%s> is disabled', $raffle->name));
        }

        return $raffle;
    }

    public function getAndVerifyIsSynchronizeAble(string $raffle_slug): Raffle
    {
        $raffle = $this->get_and_verify_raffle($raffle_slug);

        if (!$raffle->is_enabled || !$raffle->whitelabel_raffle->isEnabled) {
            throw new RuntimeException(sprintf('Raffle <%s> is disabled', $raffle->name));
        }

        return $raffle;
    }

    /**
     * @deprecated - when repo pattern will be implemented, this problem will disappear
     *
     * @param string $raffle_slug
     * @return Raffle
     */
    private function get_and_verify_raffle(string $raffle_slug): Raffle
    {
        if (!isset($this->cached_raffle[$raffle_slug])) {
            $this->cached_raffle[$raffle_slug] = $this->raffle_dao->get_by_slug_with_currency_and_rule($raffle_slug);
        }
        return $this->cached_raffle[$raffle_slug];
    }
}
