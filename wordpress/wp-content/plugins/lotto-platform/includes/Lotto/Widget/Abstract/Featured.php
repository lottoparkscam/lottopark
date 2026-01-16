<?php
if (!defined('WPINC')) {
    die;
}

abstract class Lotto_Widget_Abstract_Featured extends Lotto_Widget_Abstract_Widget
{
    protected int $default_count = 3;
}
