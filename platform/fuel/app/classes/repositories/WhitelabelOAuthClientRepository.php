<?php

declare(strict_types=1);

namespace Repositories;

use Fuel\Core\Cache;
use Fuel\Core\CacheNotFoundException;
use Fuel\Core\Database_Result;
use Helpers_Time;
use Models\WhitelabelOAuthClient;
use Repositories\Orm\AbstractRepository;

class WhitelabelOAuthClientRepository extends AbstractRepository
{
    private const CACHE_KEY = 'whitelabel_oauth_autologin_link_';

    public function __construct(WhitelabelOAuthClient $model)
    {
        parent::__construct($model);
    }

    public function findOneByClientId(string $clientId): ?array
    {
        $query = $this->db->select('whitelabel_id')
            ->from(WhitelabelOAuthClient::get_table_name())
            ->where('client_id', $clientId);

        /** @var Database_Result $result */
        $result = $query->execute();

        return $result->as_array()[0] ?? null;
    }

    public function getWhitelabelAutologinLink(int $whitelabelId): array|false
    {
        try {
            $autoLoginLink = Cache::get(self::CACHE_KEY . $whitelabelId);
        } catch (CacheNotFoundException) {
            $query = $this->db->select('name', 'autologin_uri')
                ->from(WhitelabelOAuthClient::get_table_name())
                ->where('whitelabel_id', $whitelabelId)
                ->order_by('created_at', 'desc');

            /** @var Database_Result $result */
            $result = $query->execute();

            if (empty($result->as_array()[0]['autologin_uri']) || empty($result->as_array()[0]['name'])) {
                return false;
            }

            $autoLoginLink = [
                'text' => $result->as_array()[0]['name'],
                'uri' => $result->as_array()[0]['autologin_uri'],
            ];

            Cache::set(self::CACHE_KEY . $whitelabelId, $autoLoginLink, Helpers_Time::DAY_IN_SECONDS);
        }

        return $autoLoginLink;
    }

    public function getWhitelabelOauthDomain(int $whitelabelId): string
    {
        try {
            $whitelabelDomain = Cache::get('getWhitelabelOauthDomain_' . $whitelabelId);
        } catch (CacheNotFoundException) {
            $query = $this->db->select('domain')
                ->from(WhitelabelOAuthClient::get_table_name())
                ->where('whitelabel_id', $whitelabelId);

            /** @var Database_Result $result */
            $result = $query->execute();

            if (empty($result->as_array()[0]['domain'])) {
                return '';
            }

            $whitelabelDomain = $result->as_array()[0]['domain'];

            Cache::set('getWhitelabelOauthDomain_' . $whitelabelId, $whitelabelDomain, Helpers_Time::DAY_IN_SECONDS);
        }

        return $whitelabelDomain;
    }

    public function getWhitelabelCasinoUrl(int $whitelabelId): ?string
    {
        $query = $this->db->select('casino_url')
            ->from(WhitelabelOAuthClient::get_table_name())
            ->where('whitelabel_id', $whitelabelId);

        /** @var Database_Result $result */
        $result = $query->execute();

        if (empty($result->as_array()[0]['casino_url'])) {
            return null;
        }

        return $result->as_array()[0]['casino_url'];
    }
}
