<?php
if (!defined('WPINC')) {
    die;
}

/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2020-05-07
 * Time: 16:57:55
 */
abstract class Lotto_Widget_Abstract_Widget extends WP_Widget
{

    /**
     * Form option for widget (used in form method).
     * @param array $instance instance of the widget in array form.
     * @param string $name name of the option.
     * @return string formed option, ready to set.
     */
    protected function form_option(array $instance, string $name): string
    {
        return isset($instance[$name]) ? htmlspecialchars($instance[$name]) : '';
    }

    /**
     * @param array $old_instance
     * @param array $new_instance
     * @param array $values all possible values of the option, first one will be used as default.
     * @param string $name name of the option.
     * @return Void
     */
    protected function update_option(array &$old_instance, array $new_instance, array $values, string $name): Void
    {
        $old_instance[$name] = in_array($new_instance[$name], $values) ? (int) $new_instance[$name] : $values[0];
    }
}
