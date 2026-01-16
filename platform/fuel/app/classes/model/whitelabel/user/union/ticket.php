<?php

use Fuel\Core\Database_PDO_Result;
use Fuel\Core\Database_Query;
use Fuel\Core\Date;
use Orm\RecordNotFound;
use Classes\Orm\Traits\Model_Orm_Traits_Castable;
use Classes\Orm\Interfaces\Model_Orm_Interfaces_Read;
use Models\Raffle;
use Models\WhitelabelRaffleTicket;

/**
 * Custom model for Union of whitelabel_user_ticket & whitelabel_raffle_ticket tables.
 *
 * @property int $id
 * @property int $whitelabel_id
 * @property int $whitelabel_user_id
 * @property int $whitelabel_transaction_id
 * @property string $lottery_name
 * @property string $lottery_slug
 * @property int|null $lottery_id
 * @property int|null $raffle_id
 * @property int $token
 * @property int $status
 * @property int $currency_id
 * @property string $currency_code
 * @property Date $draw_date
 * @property float $ticket_amount
 * @property float $transaction_amount
 * @property float $ticket_bonus_amount
 * @property float $transaction_bonus_amount
 * @property string $transaction_currency_code
 * @property string $transaction_currency_id
 * @property int $transaction_payment_type
 * @property float $prize
 * @property string $ip
 * @property bool $is_paid_out
 * @property bool $paid
 * @property Date $created_at
 *
 * @property bool $is_raffle
 * @property string $prefixed_token - we add R before raffle?
 * 
 * @deprecated ~ should be replaced to orm
 */
class Model_Whitelabel_User_Union_Ticket extends Model_Model implements Model_Orm_Interfaces_Read
{
    use Model_Orm_Traits_Castable;

    const EXPIRATION_TIME_IN_SECS = 10;

    protected static $_table_name = 'whitelabel_user_ticket';
    protected static $_raffle_table_name = 'whitelabel_raffle_ticket';

    /** @var Database_Query */
    private $query;

    private $mysql_chunks = [];
    private $base_sql = '';
    /** @var mixed */
    private $params;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->reset_query();
        $this->casts = [
            'id'                        => 'integer',
            'whitelabel_id'             => 'integer',
            'whitelabel_user_id'        => 'integer',
            'whitelabel_transaction_id' => 'integer',
            'token'                     => 'integer',
            'status'                    => 'integer',
            'draw_date'                 => 'datetime',
            'amount'                    => 'float',
            'prize'                     => 'float',
            'is_paid_out'               => 'boolean',
            'paid'                      => 'boolean',
            'created_at'                => 'datetime',
        ];
    }

    /**
     * @return self
     */
    public function get_one(): Model_Orm_Interfaces_Read
    {
        $results = $this->get_and_map_to_model();
        $this->reset_query();
        if (empty($results)) {
            throw new RecordNotFound();
        }
        return reset($results);
    }

    /**
     * @return self|null
     */
    public function find_one(): ?Model_Orm_Interfaces_Read
    {
        $results = $this->get_and_map_to_model();
        $this->reset_query();
        return reset($results);
    }

    /**
     * @param int|null $limit
     * @param int $offset
     *
     * @return array|self[]
     */
    public function get_results(int $limit = null, int $offset = 0): array
    {
        if ($limit || $offset) {
            $this->set_limit($limit, $offset);
        }
        return $this->get_and_map_to_model();
    }

    public function for_user(int $user_id): self
    {
        $this->add_where('whitelabel_user_id', $user_id);
        $this->add_sub_where1('whitelabel_user_id', $user_id);
        $this->add_sub_where2('whitelabel_user_id', $user_id);
        return $this;
    }

    public function exists($value, string $field = 'id'): bool
    {
        return !empty($this->add_where($field, $value)->get_results());
    }

    public function get_by_id(int $id, array $relations = []): Model_Orm_Interfaces_Read
    {
        return $this->add_where('id', $id)->get_one();
    }

    public function reload(): void
    {
        $this->reset_query();
        $result = $this->add_where('id', $this->_data['id'])->get_one();
        $this->_data = $result->to_array();
    }

    public function get_count(): int
    {
        $before_select = $this->mysql_chunks['select'];
        $this->set_select('count(*) as count');
        $this->compile_mysql();
        /** @var Database_PDO_Result|Iterator $pdo_result */
        $pdo_result = $this->query->execute();
        $this->set_select($before_select);
        return $pdo_result->current()['count'];
    }

    public function set_order_by(string $sort_field = 'id', string $sort_order = 'asc'): Model_Orm_Interfaces_Read
    {
        $this->mysql_chunks['order'] = ['s.' . $sort_field, $sort_order];
        return $this;
    }

    public function only_pending(): self
    {
        $this->add_where('status', Helpers_General::TICKET_STATUS_PENDING)
        ->add_where('paid', true);
        return $this;
    }

    public function only_past(): self
    {
        $this->add_where('status', Helpers_General::TICKET_STATUS_PENDING, '!=')
        ->add_where('paid', true);
        return $this;
    }

    public function filter_status(int $status): self
    {
        $this->add_where('status', $status);
        return $this;
    }

    private function get_is_raffle_attribute(): bool
    {
        return !empty($this->raffle_id);
    }

    private function get_prefixed_token_attribute(): string
    {
        if ($this->is_raffle) {
            return sprintf('R%d', $this->token);
        }
        return sprintf('T%d', $this->token);
    }

    private function get_transaction_amount_attribute(): string
    {
        return empty($this->_data['transaction_amount']) ? $this->ticket_amount : $this->_data['transaction_amount'];
    }

    private function get_transaction_bonus_amount_attribute(): string
    {
        return empty($this->_data['transaction_bonus_amount']) ? $this->ticket_bonus_amount : $this->_data['transaction_bonus_amount'];
    }

    private function get_transaction_currency_code_attribute(): string
    {
        return empty($this->_data['transaction_currency_code']) ? $this->currency_code : $this->_data['transaction_currency_code'];
    }

    private function compile_mysql(): void
    {
        $select = $this->mysql_chunks['select'];

        $where = $this->compile_where('where');
        $sub_where1 = $this->compile_where('sub_where1');
        $sub_where2 = $this->compile_where('sub_where2');

        $order = vsprintf('ORDER BY %s %s', $this->mysql_chunks['order']);
        $limit = vsprintf('LIMIT %d OFFSET %d', $this->mysql_chunks['limit']);

        $this->query = new Database_Query(str_replace([':select', ':table1', ':table2', ':sub_where1', ':sub_where2', ':where', ':order', ':limit'], [
            $select,
            $this::$_table_name,
            $this::$_raffle_table_name,
            $sub_where1,
            $sub_where2,
            $where,
            $order,
            $limit
        ], $this->base_sql));
    }

    private function compile_where(string $where_chunk_name): string
    {
        $where = !empty($this->mysql_chunks[$where_chunk_name]) ? 'WHERE ' : '';
        $where .= implode(' AND ', array_map(function (array $where) {
            [$field, $operator, $value] = $where;
            return sprintf('%s %s %s', $field, $operator, $value);
        }, $this->mysql_chunks[$where_chunk_name]));
        return $where;
    }

    public function add_where(string $field, $value, string $operator = '='): self
    {
        $this->mysql_chunks['where'][] = ['s.' . $field, $operator, $value];
        return $this;
    }

    private function add_sub_where1(string $field, $value, string $operator = '='): void
    {
        $this->mysql_chunks['sub_where1'][] = [$field, $operator, $value];
    }

    private function add_sub_where2(string $field, $value, string $operator = '='): void
    {
        $this->mysql_chunks['sub_where2'][] = [$field, $operator, $value];
    }

    private function set_select(string $select): self
    {
        $this->mysql_chunks['select'] = $select;
        return $this;
    }

    private function set_limit(int $limit, int $offset = 0): void
    {
        $this->mysql_chunks['limit'] = [$limit, $offset];
    }

    private function get_and_map_to_model(): array
    {
        $this->compile_mysql();

        $pdo_result = Helpers_Cache::read_or_create($this->query, null, self::EXPIRATION_TIME_IN_SECS);

        $this->reset_query();

        if (empty($pdo_result)) {
            return [];
        }

        return array_map(function (array $model_data) {
            return new self($model_data);
        }, $pdo_result);
    }

    private function reset_query(): void
    {
        $this->mysql_chunks = [
            'select'     => '
            s.*, 
            currency.code as currency_code, 
            IF(raffle.name IS NOT NULL, raffle.name, lottery.name) as lottery_name, 
            IF(raffle.slug IS NOT NULL, raffle.slug, lottery.slug) as lottery_slug,
            whitelabel_transaction.amount as transaction_amount,
            whitelabel_transaction.bonus_amount as transaction_bonus_amount,
            whitelabel_transaction.payment_currency_id as transaction_currency,
            whitelabel_transaction.payment_method_type as transaction_payment_type,
            whitelabel_transaction.amount_usd,
            whitelabel_transaction.bonus_amount_usd,
            wt_currency.code as transaction_currency_code
            ',
            'where'      => [],
            'sub_where1' => [],
            'sub_where2' => [],
            'limit'      => [10, 0],
            'order'      => ['amount', 'asc']
        ];

        $this->base_sql = /** @lang MySql */
            '
        SELECT :select
        
        FROM
        (
            SELECT id,
            whitelabel_id,
            whitelabel_user_id,
            whitelabel_transaction_id,
            lottery_id,
            null as raffle_id,
            token,
            status,
            currency_id,
            draw_date,
            :table1.amount as ticket_amount,
            :table1.bonus_amount as ticket_bonus_amount,
            prize,
            ip,
            payout as is_paid_out,
            paid,
            date   as created_at
            FROM :table1
            :sub_where1
            
            UNION
            
            SELECT id,
            whitelabel_id,
            whitelabel_user_id,
            whitelabel_transaction_id,
            null as lottery_id,
            raffle_id,
            token,
            status,
            currency_id,
            draw_date,
            :table2.amount as ticket_amount,
            :table2.bonus_amount as ticket_bonus_amount,
            prize,
            ip,
            is_paid_out,
            1 as paid,
            created_at
            FROM :table2
            :sub_where2
        ) as s
        
        LEFT JOIN currency ON currency_id = currency.id
        LEFT JOIN raffle ON s.raffle_id = raffle.id
        LEFT JOIN lottery ON s.lottery_id = lottery.id
        LEFT JOIN whitelabel_transaction ON whitelabel_transaction.id = s.whitelabel_transaction_id
        LEFT JOIN currency as wt_currency ON whitelabel_transaction.currency_id = wt_currency.id
        :where
        :order
        :limit
        ';
    }

    public function disable_casting(): self
    {
        $this->is_casting_enabled = false;
        return $this;
    }

    public function enable_casting(): self
    {
        $this->is_casting_enabled = true;
        return $this;
    }

    /**
     * Use this function to fetch uncasted properties.
     * @return mixed
     */
    public function propertyRaw(string $name)
    {
        $this->disable_casting();
        $value = $this->{$name};
        $this->enable_casting();
        return $value;
    }

    public function isNotFaireumRaffle(): bool
    {
        return $this->lottery_slug !== Raffle::FAIREUM_RAFFLE_SLUG;
    }

    public function isRaffleWithInKindPrize(): bool
    {
        return $this->lottery_slug === Raffle::FAIREUM_RAFFLE_SLUG || $this->lottery_slug === Raffle::LOTTERYKING_RAFFLE_SLUG || $this->lottery_slug === Raffle::MONKS_RAFFLE_SLUG;
    }

    public function isRaffleWithoutInKindPrize(): bool
    {
        return !$this->isRaffleWithInKindPrize();
    }

    public function isWin(): bool
    {
        return $this->status === WhitelabelRaffleTicket::STATUS_WIN;
    }
}
