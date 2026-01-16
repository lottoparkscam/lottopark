<?php


namespace Services\Shared;

use DateTimeImmutable;
use DateTimeInterface;
use Fuel\Core\Fuel;
use Helpers\UrlHelper;
use Models\Whitelabel;

/**
 * Class System
 * contains commonly used System data (injected in container-config.php).
 */
class System
{
    private string $env;
    private DateTimeInterface $date;
    private ?Whitelabel $whitelabel;
    private array $urlData;

    public function __construct(
        string $env,
        DateTimeImmutable $date,
        array $urlData,
        ?Whitelabel $whitelabel = null
    ) {
        $this->env = $env;
        $this->date = $date;
        $this->whitelabel = $whitelabel;
        $this->urlData = $urlData;
    }

    public function whitelabel(): ?Whitelabel
    {
        return $this->whitelabel;
    }

    /**
     * Must contains protocol
     *
     * @return string
     */
    public function baseFullUrl(): string
    {
        if (empty($this->whitelabel)) {
            $domain = $this->urlData['host'];
            return $this->normalizeUrl($domain);
        }

        $domain = $this->whitelabel->domain;

        return $this->normalizeUrl($domain);
    }

    /**
     * todo st: move to helper, use DI protocol
     * @param string $domain
     * @return string
     */
    private function normalizeUrl(string $domain): string
    {
        $scheme = (!empty($this->urlData['scheme']) ? $this->urlData['scheme'] : 'http') . '://';
        if (strpos($domain, 'http') === false) {
            $url = $scheme . $domain;
        } else {
            $url = $domain;
        }

        $url = UrlHelper::addWwwPrefixIfNeeded($url);

        # force add trailing slash at the end if missing
        $url .= (substr($url, -1) == '/' ? '' : '/');
        return $url;
    }

    public function env(): string
    {
        return $this->env;
    }

    public function is_production_env(): bool
    {
        return $this->env() === Fuel::PRODUCTION;
    }

    public function date(): DateTimeImmutable
    {
        return $this->date;
    }
}
