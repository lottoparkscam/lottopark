<?php

namespace Repositories;

use Carbon\Carbon;
use Classes\Orm\Criteria\With\Model_Orm_Criteria_With_Relation;
use Container;
use Exception;
use Fuel\Core\Database_Query_Builder;
use Models\Currency;
use Models\LotteryType;
use Models\Whitelabel;
use Throwable;
use Helpers_Time;
use Models\Lottery;
use Fuel\Core\Cache;
use Fuel\Core\CacheNotFoundException;
use Repositories\Orm\AbstractRepository;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Classes\Orm\Criteria\Model_Orm_Criteria_Select;

/**
 * @method findOneBySlug($lotterySlug)
 * @method Lottery findOneByType(string $type)
 * @method Lottery findOneById(int $id)
 */
class LotteryRepository extends AbstractRepository
{
    public const CACHE_KEY_GG_WORLD_LOTTERIES_SELECTED = 'gg_world_lotteries_selected';

    public function __construct(Lottery $model)
    {
        parent::__construct($model);
    }

    /** @throws Throwable on database error */
    public function getGGWorldSelectedLotteries(): array
    {
        try {
            $ggWorldLotteries = Cache::get(LotteryRepository::CACHE_KEY_GG_WORLD_LOTTERIES_SELECTED);
        } catch (CacheNotFoundException $exception) {
            /** @var mixed $results */
            $results = $this->db->selectArray([
                ['lottery.id', 'id'],
                'playable',
                'is_enabled',
                'timezone',
                'draw_dates',
                'name',
                'next_date_local',
                'slug',
                'current_jackpot',
                ['currency_model.code', 'currency'],
                ['force_currency_model.code', 'force_currency'],
            ])
                ->from($this->model::get_table_name())
                ->join(['currency', 'currency_model'], 'LEFT')
                ->on('lottery.currency_id', '=', 'currency_model.id')
                ->join(['currency', 'force_currency_model'], 'LEFT')
                ->on('lottery.force_currency_id', '=', 'force_currency_model.id')
                ->where('slug', 'IN', ['double-jack', 'double-jack-keno', 'double-jack-x', 'gg-world', 'gg-world-keno', 'gg-world-x'])
                ->and_where('is_enabled', '=', 1)
                ->and_where('playable', '=', 1)
                ->execute();
            $ggWorldLotteries = $results->as_array();
            Cache::set(LotteryRepository::CACHE_KEY_GG_WORLD_LOTTERIES_SELECTED, $ggWorldLotteries, Helpers_Time::DAY_IN_SECONDS);
        }
        $normalLotteries = [];
        $doubleJackLotteries = [];
        $doubleJackLotteriesSlug = ['double-jack', 'double-jack-keno', 'double-jack-x',];
        foreach ($ggWorldLotteries as $ggWorldLottery) {
            $isDoubleJackLottery = in_array($ggWorldLottery['slug'], $doubleJackLotteriesSlug);
            if ($isDoubleJackLottery) {
                $doubleJackLotteries[] = $ggWorldLottery;
            } else {
                $normalLotteries[] = $ggWorldLottery;
            }
        }
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $isDoubleJack = $whitelabel->isTheme(Whitelabel::DOUBLEJACK_THEME);
        if ($isDoubleJack) {
            return $doubleJackLotteries;
        }
        return $normalLotteries;
    }

    public function getIdBySlug(string $slug): ?int
    {
        $this->pushCriterias([
            new Model_Orm_Criteria_Select(['id']),
            new Model_Orm_Criteria_Where('slug', $slug)
        ]);

        return $this->findOne()->id ?? null;
    }

    public function findEnabledByWhitelabelIdForInfoBoxes(int $whitelabelId, array $lotteriesSlugsInMenu): array
    {
        /** @var mixed $results */
        $results = $this->db->selectArray([
            ['lottery.id', 'id'],
            'name',
            'slug',
            'current_jackpot',
            'current_jackpot_usd',
            'price',
            ['lottery.timezone', 'timezone'],
            'next_date_local',
            'draw_dates',
            ['currency.code', 'currency'],
            ['force_currency.code', 'force_currency'],
            ['whitelabel_lottery.quick_pick_lines', 'quick_pick_lines'],
            ['whitelabel_lottery.model', 'model'],
            ['whitelabel_lottery.tier', 'tier'],
            ['whitelabel_lottery.volume', 'volume'],
            ['whitelabel_lottery.income_type', 'income_type'],
            ['whitelabel_lottery.income', 'income'],
            ['lottery_provider.fee', 'fee'],
            ['lottery_provider.provider', 'provider']
        ])
            ->from($this->model::get_table_name())
            ->join('whitelabel_lottery', 'LEFT')
            ->on('whitelabel_lottery.whitelabel_id', '=', $this->db->expr($whitelabelId))
            ->and_on('whitelabel_lottery.lottery_id', '=', 'lottery.id')
            ->join(['currency', 'currency'], 'LEFT')
            ->on('lottery.currency_id', '=', 'currency.id')
            ->join(['currency', 'force_currency'], 'LEFT')
            ->on('lottery.force_currency_id', '=', 'force_currency.id')
            ->join('lottery_provider', 'LEFT')
            ->on('whitelabel_lottery.lottery_provider_id', '=', 'lottery_provider.id')
            ->where('lottery.is_enabled', '=', true)
            ->and_where('lottery.is_temporarily_disabled', '=', false)
            ->and_where('whitelabel_lottery.is_enabled', '=', true)
            ->and_where('lottery.slug', 'IN', $lotteriesSlugsInMenu)
            ->execute();

        return $results->as_array();
    }

    public function findEnabledForCurrentWhitelabel(string $lotterySlug = null): array
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $whitelabelId = $whitelabel->id;

        /** @var mixed $query */
        $query = $this->db->selectArray([
            ['lottery.id', 'id'],
            'name',
            'slug',
            'playable',
            'type',
            'current_jackpot',
            'current_jackpot_usd',
            'price',
            ['lottery.timezone', 'timezone'],
            'last_date_local',
            'next_date_local',
            'additional_data',
            'last_numbers',
            'last_bnumbers',
            'draw_dates',
            ['currency.code', 'currency'],
            ['force_currency.code', 'force_currency'],
            ['whitelabel_lottery.quick_pick_lines', 'quick_pick_lines'],
            ['whitelabel_lottery.model', 'model'],
            ['whitelabel_lottery.tier', 'tier'],
            ['whitelabel_lottery.volume', 'volume'],
            ['whitelabel_lottery.income_type', 'income_type'],
            ['whitelabel_lottery.income', 'income'],
            ['lottery_provider.fee', 'fee'],
            ['lottery_provider.provider', 'provider'],
            ['lottery_provider.provider', 'multiplier']
        ])
            ->from($this->model::get_table_name())
            ->join('whitelabel_lottery', 'LEFT')
            ->on('whitelabel_lottery.whitelabel_id', '=', $this->db->expr($whitelabelId))
            ->and_on('whitelabel_lottery.lottery_id', '=', 'lottery.id')
            ->join(['currency', 'currency'], 'LEFT')
            ->on('lottery.currency_id', '=', 'currency.id')
            ->join(['currency', 'force_currency'], 'LEFT')
            ->on('lottery.force_currency_id', '=', 'force_currency.id')
            ->join('lottery_provider', 'LEFT')
            ->on('whitelabel_lottery.lottery_provider_id', '=', 'lottery_provider.id')
            ->where('lottery.is_enabled', '=', true)
            ->and_where('lottery.is_temporarily_disabled', '=', false)
            ->and_where('whitelabel_lottery.is_enabled', '=', true);

        if (!is_null($lotterySlug)) {
            $query->and_where('lottery.slug', '=', $lotterySlug);
        }

        $results = $query->execute();

        return $results->as_array();
    }

    /**
     * Lottery that has the flag is_playable = false.
     * There are no numbers to choose from, but it has tabs for results and information.
     */
    public function setLotteryUnplayableBySlug(string $slug): bool
    {
        /** @var mixed $result */
        $result = $this->db->update($this->model::get_table_name())
            ->set(['playable' => false])
            ->where('slug', '=', $slug)
            ->execute();

        if ($result !== 1) {
            return false;
        }

        return true;
    }

    public function setLotteryPlayableBySlug(string $slug): bool
    {
        /** @var mixed $result */
        $result = $this->db->update($this->model::get_table_name())
            ->set(['playable' => true])
            ->where('slug', '=', $slug)
            ->execute();

        if ($result !== 1) {
            return false;
        }

        return true;
    }

    public function findWaitingForDraw(): array
    {
        /** @var Database_Query_Builder $results */
        $results = $this->db->selectArray([
            ['lottery.id', 'id'],
            'name',
            'draw_dates',
            'slug',
            'next_date_local',
            'type',
            'timezone',
            ['currency.code', 'currency_code'],
        ])
            ->from($this->model::get_table_name())
            ->join(Currency::get_table_name(), 'INNER')
            ->on('lottery.currency_id', '=', 'currency.id')
            ->where('lottery.is_enabled', '=', true)
            ->and_where('lottery.next_date_utc', '<', Carbon::now('UTC')->format(Helpers_Time::DATETIME_FORMAT))
            ->execute();

        return $results->as_array();
    }

    public function findLotteriesWhereNextDrawDateEqualsLastDrawDate(): array
    {
        $query = $this->db->select('slug')
            ->from(Lottery::get_table_name())
            ->where($this->db->expr('`last_date_local` >= `next_date_local` OR `next_date_local` IS NULL OR `last_date_local` IS NULL'));

        /** @var Database_Query_Builder $results */
        $results = $query->execute();
        return $results->as_array();
    }

    public function getLotteryNamesByIds(array $ids): array
    {
        $query = $this->db->select('name', 'id')
            ->from(Lottery::get_table_name())
            ->where('id', 'in', $ids);

        /** @var Database_Query_Builder $results */
        $results = $query->execute();
        return $results->as_array();
    }

    public function getLotteryDataForBannerByLotteryId(int $id): array
    {
        $query = $this->db->selectArray(['lottery.name', 'lottery.current_jackpot', ['currency_model.code', 'currency']])
            ->from(Lottery::get_table_name())
            ->join(['currency', 'currency_model'], 'LEFT')
            ->on('lottery.currency_id', '=', 'currency_model.id')
            ->where('lottery.id', $id);

        /** @var Database_Query_Builder $results */
        $results = $query->execute();
        return $results->as_array()[0] ?? [];
    }
}
