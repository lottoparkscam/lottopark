<?php


use Fuel\Core\Fieldset as Fieldset_Base;

final class Fieldset extends Fieldset_Base
{
    /**
     * Create Fieldset object
     *
     * @param   string    $name    Identifier for this fieldset
     * @param   array     $config  Configuration array
     * @return  Fieldset|Fieldset_Base
     */
    public static function forge($name = 'default', array $config = [])
    {
        if ($exists = self::instance($name))
        {
            \Errorhandler::notice('Fieldset with this name exists already, cannot be overwritten.');
            return $exists;
        }

        self::$_instances[$name] = new self($name, $config);

        if ($name == 'default')
        {
            self::$_instance = self::$_instances[$name];
        }

        return self::$_instances[$name];
    }

    /**
     * Return a specific instance, or the default instance (is created if necessary)
     *
     * @param Fieldset|Fieldset_Base|string|null $instance
     *
     * @return  Fieldset|false
     */
    public static function instance($instance = null)
    {
        if ($instance !== null)
        {
            $name = null;
            if ($instance instanceof Fieldset || $instance instanceof Fieldset_Base){
                $name = $instance->name;
            }elseif (is_string($instance)){
                $name = $instance;
            } else{
                throw new Exception('Parameter 1 passed to function Fieldset::instance is neither an object of class Fieldset nor a string');
            }
            if ( ! array_key_exists($name, self::$_instances))
            {
                return false;
            }

            return self::$_instances[$name];
        }

        if (self::$_instance === null)
        {
            self::$_instance = self::forge();
        }

        return self::$_instance;
    }
}