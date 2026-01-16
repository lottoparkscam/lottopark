<?php
if (!defined('WPINC')) {
    die;
}

/**
 * Class encapsulates settings for Widget's Instance.
 * Some props are stored in DB (like customizable global stuff like background image, or sizes etc)
 * and other ones are initializable per instance.
 * The main idea of using this data is to provide one flexible place for all resolved values.
 *
 * Use phpdoc "@property" in parent class to help the others to access defined options.
 */
abstract class Lotto_Widget_Abstract_Settings
{
    /**
     *  Must be defined in parent class.
     *  It contains the column name in DB where data is stored.
     *  For example: theme_mods_lottopark column from wp_2_options.
     */
    public const DB_SETTING_NAME = null;

    /**
     * @var array - widget $instance
     */
    protected $settings = [];

    /**
     * @var array - global settings stored in db (accessible by __get with prefix)
     */
    protected $db_settings = [];

    /**
     * Abstract_Widget_Settings constructor.
     *
     * @param array $settings
     *
     * @throws BadMethodCallException
     */
    final public function __construct(array $settings = [])
    {
        if (static::DB_SETTING_NAME === null) {
            throw new BadMethodCallException('DB_SETTING_NAME must be defined in parent setting class');
        }
        $db_options = get_option(static::DB_SETTING_NAME);
        $this->db_settings = is_array($db_options) ? $db_options : [];
        $this->settings = $settings;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    final public function __get(string $name)
    {
        $sources = [$this->settings, $this->db_settings, $this->defaults()];
        foreach ($sources as $source) {
            $unique_name = $this::as_db_unique_key($name);

            # last iteration is default values from parent class definition, then we can fetch data from there - if not defined, error occurs
            $is_default_source = !next($sources);
            if ($is_default_source) {
                if (array_key_exists($name, $source)) {
                    return $source[$name];
                }
                if (array_key_exists($unique_name, $source)) {
                    return $source[$unique_name];
                }
            }

            # the all others props from settings and db_settings must be defined (no '' is allowed)
            if (isset($source[$name]) && empty($source[$name]) === false) {
                return $source[$name];
            }
            if (isset($source[$unique_name]) && empty($source[$unique_name]) === false) {
                return $source[$unique_name];
            }
        }
        throw new InvalidArgumentException(sprintf('Given <%s> or <%s> property is not defined', $name, $this::as_db_unique_key($name)));
    }

    final public function __isset(string $name): bool
    {
        $unique_name = $this::as_db_unique_key($name);
        $values = array_merge($this->defaults(), $this->db_settings, $this->settings);
        return isset($values[$name], $values[$unique_name]);
    }

    public function toArray(): array
    {
        return $this->settings;
    }

    /**
     *  Gets unique name id for customizer.php (data stored in DB).
     *  Returned value should be equal to _Customizer's names.
     *
     * @param string $name
     *
     * @return string
     */
    abstract protected static function as_db_unique_key(string $name): string;

    final public function __toArray(): array
    {
        return $this->toArray();
    }

    /**
     * Key => value array with default values.
     * It is called by magic __get when no data is present in settings and db_settings.
     *
     * @return array
     */
    protected function defaults(): array
    {
        return [];
    }
}
