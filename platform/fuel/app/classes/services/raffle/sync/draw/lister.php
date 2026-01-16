<?php

use Models\RaffleDraw;
use Repositories\RaffleDrawRepository;

/**
 * Repeats iteration throw draws api until all unsycned draws will be fetched.
 */
class Services_Raffle_Sync_Draw_Lister
{
    public const DRAWS_API_QUERY_LIMIT = 10;

    private Services_Lcs_Draws_Contract $draws_api;
    private RaffleDraw $draw_dao;
    private RaffleDrawRepository $raffleDrawRepository;

    public function __construct(Services_Lcs_Draws_Contract $draws_api, RaffleDraw $draw)
    {
        $this->draws_api = $draws_api;
        $this->draw_dao = $draw;
        $this->raffleDrawRepository = Container::get(RaffleDrawRepository::class);
    }

    public function get_all_lcs_unsynchronized_draws(string $raffle_slug, string $raffle_type): array
    {
        $LCSDraws = [];
        $offset = 0;

        $lastDrawNumber = $this->raffleDrawRepository->getLastDrawNumberByRaffleSlug($raffle_slug);
        $drawsFromLCS = $this->draws_api->request($raffle_slug, $raffle_type, self::DRAWS_API_QUERY_LIMIT, $offset, $lastDrawNumber)->get_body();
        foreach ($drawsFromLCS as $draw) {
            if ($this->check_draw_exists($raffle_slug, $draw['draw_no'])) {
                continue;
            }
            $LCSDraws[] = $draw;
        }

        return $LCSDraws;
    }

    private function check_draw_exists(string $raffle_slug, int $draw_no): bool
    {
        return $this->draw_dao->check_raffle_draw_exists($raffle_slug, $draw_no);
    }
}
