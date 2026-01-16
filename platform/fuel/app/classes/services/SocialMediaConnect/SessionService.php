<?php

namespace Services\SocialMediaConnect;

use Fuel\Core\Session;
use Hybridauth\Storage\StorageInterface;

/** This session should work like platform/fuel/vendor/hybridauth/hybridauth/src/Storage/Session.php */
class SessionService implements StorageInterface
{
    private const STORE_NAMESPACE = 'HYBRIDAUTH::STORAGE';

    public function get($key): mixed
    {
        $key = strtolower($key);
        $currentHybridAuthSession = Session::get(self::STORE_NAMESPACE);
        if (!is_null($currentHybridAuthSession) && isset($currentHybridAuthSession[$key])) {
            $value = $currentHybridAuthSession[$key];

            if (is_array($value) && array_key_exists('lateObject', $value)) {
                $value = unserialize($value['lateObject']);
            }

            return $value;
        }

        return null;
    }

    public function set($key, $value): void
    {
        $key = strtolower($key);
        if (is_object($value)) {
            // We encapsulate as our classes may be defined after session is initialized.
            $value = ['lateObject' => serialize($value)];
        }

        $currentHybridAuthSession = Session::get(self::STORE_NAMESPACE, []);
        $currentHybridAuthSession[$key] = $value;

        Session::set(self::STORE_NAMESPACE, $currentHybridAuthSession);
    }

    public function delete($key): void
    {
        $key = strtolower($key);
        $currentHybridAuthSession = Session::get(self::STORE_NAMESPACE);
        if (!is_null($currentHybridAuthSession) && isset($currentHybridAuthSession[$key])) {
            unset($currentHybridAuthSession[$key]);
        }
        Session::set(self::STORE_NAMESPACE, $currentHybridAuthSession);
    }

    public function deleteMatch($key): void
    {
        $key = strtolower($key);
        $currentHybridAuthSession = Session::get(self::STORE_NAMESPACE);

        if (!is_null($currentHybridAuthSession) && count($currentHybridAuthSession)) {

            foreach ($currentHybridAuthSession as $k => $v) {
                if (strstr($k, $key)) {
                    unset($currentHybridAuthSession[$k]);
                }
            }
            Session::set(self::STORE_NAMESPACE, $currentHybridAuthSession);
        }
    }

    public function clear(): void
    {
        Session::set(self::STORE_NAMESPACE, []);
    }
}
