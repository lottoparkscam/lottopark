<?php

use Models\Raffle;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;

class Services_Raffle_Logger
{
    const TYPE_INFO = 0;
    const TYPE_SUCCESS = 1;
    const TYPE_WARNING = 2;
    const TYPE_ERROR = 3;

    private Model_Raffle_Log $log_dao;
    private Raffle $raffle_dao;

    /** @var array|Closure[] */
    private $subscribers = [];

    /**
     * key => raffle->id, value = raffle
     * Tiny approach to reduce DB calls, when logger is invoked in many places.
     *
     * @var array
     */
    private array $raffles_cache = [];

    public function __construct(Model_Raffle_Log $log, Raffle $raffle)
    {
        $this->log_dao = $log;
        $this->raffle_dao = $raffle;
    }

    private function log(int $type, Raffle $raffle, string $message): void
    {
        $this->log_dao::add_log($type, $raffle->id, $message);
        $this->notify($type, $message);
    }

    public function log_info($raffle_id_or_slug, string $message): void
    {
        $this->log(self::TYPE_INFO, $this->get_raffle_by_id_or_slug($raffle_id_or_slug), $message);
    }

    public function log_success($raffle_id_or_slug, string $message): void
    {
        $this->log(self::TYPE_SUCCESS, $this->get_raffle_by_id_or_slug($raffle_id_or_slug), $message);
    }

    public function log_warning($raffle_id_or_slug, string $message): void
    {
        $this->log(self::TYPE_WARNING, $this->get_raffle_by_id_or_slug($raffle_id_or_slug), $message);
    }

    public function log_error($raffle_id_or_slug, string $message): void
    {
        $this->log(self::TYPE_ERROR, $this->get_raffle_by_id_or_slug($raffle_id_or_slug), $message);
    }

    private function get_raffle_by_id_or_slug($raffle_id_or_slug): Raffle
    {
        $raffle_identifier = is_integer($raffle_id_or_slug) ? 'id' : 'slug';
        if (isset($this->raffles_cache[$raffle_identifier])) {
            return $this->raffles_cache[$raffle_identifier];
        }
        $criteria = new Model_Orm_Criteria_Where($raffle_identifier, $raffle_id_or_slug);
        $raffle = $this->raffle_dao->push_criteria($criteria)->get_one();
        $this->raffles_cache[$raffle_identifier] = $raffle;
        return $raffle;
    }

    public function subscribe(Closure $closure): void
    {
        $this->subscribers[] = $closure;
    }

    private function notify(int $type, string $message)
    {
        switch ($type) {
            case self::TYPE_INFO: $type_as_string = 'INFO'; break;
            case self::TYPE_SUCCESS: $type_as_string = 'SUCCESS'; break;
            case self::TYPE_WARNING: $type_as_string = 'WARNING'; break;
            case self::TYPE_ERROR: $type_as_string = 'ERROR'; break;
            default:
                throw new InvalidArgumentException();
        }
        foreach ($this->subscribers as $subscriber) {
            call_user_func($subscriber, sprintf('[%s] %s', $type_as_string, $message));
        }
    }
}
