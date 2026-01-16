<?php

namespace Services;

use Fuel\Core\Cookie;
use Helpers_General;
use Models\Whitelabel;

class AffService
{
	public function getPropertyFromCookie(string $name): ?string
	{
		return Cookie::get($name) ?? null;
	}

	/**
	 * @deprecated use savePropertyToCookie in new code.
	 */
	public function savePropertyToCookieLegacy(array $whitelabel, string $name, string $value): bool
	{
		return $this->savePropertyToCookie(new Whitelabel($whitelabel), $name, $value);
	}

	public function savePropertyToCookie(Whitelabel $whitelabel, string $name, string $value): bool
	{
		// save cookie or session corresponding to setting
		switch ($whitelabel->affRefLifetime) {
			default:
			case 0: // unlimited
				$cookieTimeout = 31104000 * 99; // 99 years
				break;
			case 1: // 1 day
				$cookieTimeout = 86400;
				break;
			case 2: // 3 day
				$cookieTimeout = 259200;
				break;
			case 3: // 1 week
				$cookieTimeout = 604800;
				break;
			case 4: // 2 weeks
				$cookieTimeout = 1209600;
				break;
			case 5: // 1 month
				$cookieTimeout = 2592000;
				break;
			case 6: // 3 months
				$cookieTimeout = 7776000;
				break;
			case 7: // 6 months
				$cookieTimeout = 15552000;
				break;
			case 8: // 1 year
				$cookieTimeout = 31104000;
				break;
		}

        // we set a domain to access cookies from subdomains.
        $cookieWhitelabelDomain = '.' . $whitelabel->domain;

		return Cookie::set($name, $value, $cookieTimeout, '/', $cookieWhitelabelDomain, true, true);
	}

	/**
     * Save ref, based on settings.
     * @param string $token
     * @param array $whitelabel array containing settings.
     */
    public function saveRef(string $token, array $whitelabel): bool
    {
		$whitelabel = Whitelabel::find($whitelabel['id']);
        return $this->savePropertyToCookie(
			$whitelabel,
			Helpers_General::COOKIE_AFF_NAME,
			$token,
		);
    }

	/**
	 * Save Tag Marketing data to cookie
	 * @param string $clickId (32 characters)
	 * @param Whitelabel $whitelabel whitelabel data
	 * 
	 * @return bool
	 */
	public function saveTagMarketingData(string $clickId, Whitelabel $whitelabel): bool
	{
		if (empty($clickId) || strlen($clickId) !== 32) {
			return false;
		}
		return $this->savePropertyToCookie(
			$whitelabel,
			Helpers_General::COOKIE_AFF_TAG_MARKETING_TRANSACTION_ID,
			$clickId,
		);
	}

	/**
	 * Save Digital Hub data to cookie
	 * @param string $clickId (not constant length but up to 255 characters limited by database column)
	 * @param Whitelabel $whitelabel whitelabel data
	 * 
	 * @return bool
	 */
	public function saveDigitalHubData(string $clickId, Whitelabel $whitelabel): bool
	{
		if (empty($clickId) || strlen($clickId) > 255) {
			return false;
		}
		return $this->savePropertyToCookie(
			$whitelabel,
			Helpers_General::COOKIE_AFF_DIGITAL_HUB_DATA,
			$clickId,
		);
	}

	/**
	 * Save Tibolario data to cookie
	 * @param string $clickId (not constant length but up to 255 characters limited by database column)
	 * @param Whitelabel $whitelabel whitelabel data
	 * 
	 * @return bool
	 */
	public function saveTibolarioData(string $clickId, Whitelabel $whitelabel): bool
	{
		if (empty($clickId) || strlen($clickId) > 255) {
			return false;
		}
		return $this->savePropertyToCookie(
			$whitelabel,
			Helpers_General::COOKIE_AFF_TIBOLARIO,
			$clickId,
		);
	}

	/**
	 * Save LoudingAds data to cookie
	 * @param string $clickId (not constant length but up to 255 characters limited by database column)
	 * @param Whitelabel $whitelabel whitelabel data
	 * 
	 * @return bool
	 */
	public function saveLoudingAdsData(string $clickId, Whitelabel $whitelabel): bool
	{
		if (empty($clickId) || strlen($clickId) > 255) {
			return false;
		}
		return $this->savePropertyToCookie(
			$whitelabel,
			Helpers_General::COOKIE_AFF_LOUDING_ADS,
			$clickId,
		);
	}

	/**
	 * Save TAGD data to cookie
	 * @param string $clickId (not constant length but up to 255 characters limited by database column)
	 * @param Whitelabel $whitelabel whitelabel data
	 * 
	 * @return bool
	 */
	public function saveTagdData(string $clickId, Whitelabel $whitelabel): bool
	{
		if (empty($clickId) || strlen($clickId) > 255) {
			return false;
		}
		return $this->savePropertyToCookie(
			$whitelabel,
			Helpers_General::COOKIE_AFF_TAGD,
			$clickId,
		);
	}
}
