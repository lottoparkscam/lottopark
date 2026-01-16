<?php

/**
 * Singleton pattern helper class
 */
class Lotto_Settings
{

    /**
     *
     * @var Lotto_Settings
     */
    private static $instance;

    /**
     * This is the proper place to store
     * and share different settings for whole application
     *
     * @var array
     */
    private $data = [];

    /**
     * Block of possibility to create Lotto_Settings
     * outside the inner static function - see Singleton Pattern
     */
    private function __construct()
    {
    }

    /**
     * Block of possibility to clone Lotto_Settings object
     * - see Singleton Pattern
     */
    private function __clone()
    {
    }

    /**
     * Store new setting by the given key
     *
     * @param Mixed $key
     * @param Mixed $data
     */
    public function set($key, $data)
    {
        $this->data[$key] = $data;
    }
    
    /**
     * Remove settings by the given key.
     * @param string key.
     */
    public function remove($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Get previously saved setting by key or return null
     * if void of key in the settings array.
     *
     * @param Mixed $key
     * @return null|Mixed
     */
    public function get($key)
    {
        if (!isset($this->data[$key])) {
            return null;
        }
        return $this->data[$key];
    }

    /**
     * Create only once instance of Lotto_Settings class and always
     * return only that instance if asked - see Singleton Pattern
     *
     * @return Lotto_Settings
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Lotto_Settings();
        }
        return self::$instance;
    }
}
