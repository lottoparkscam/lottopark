<?php

use Fuel\Core\Validation;

/**
 * Archetype of validators.
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 */
abstract class Validator_Validator
{
    
    /**
     * Validate input.
     * @return bool true if valid.
     */
    public function is_valid(): bool
    {
        return $this->build_validation()
            ->run();
    }

    /**
     * Build validation object for validator
     * @return Validation
     */
    public abstract function build_validation(): Validation;
    
    /**
     * Build and run validator.
     * @param mixed $args args for object constructor.
     * @return bool true if valid.
     */
    public static function validate(...$args): bool
    {
        $child_class = get_called_class();
        return (new $child_class(...$args))
            ->is_valid(); // TODO: we could wrap it and catch all exceptions etc.
    }

    /**
     * Build validation.
     * @param mixed $args args for object constructor.
     * @return Validation validation object.
     */
    public static function validation(...$args): Validation
    {
        $child_class = get_called_class();
        return (new $child_class(...$args))
            ->build_validation(); // TODO: we could wrap it and catch all exceptions etc.
    }

}
