<?php
if (!defined('WPINC')) {
    die;
}

/**
* @author Marcin Klimek <marcin.klimek at gg.international>
* Date: 2020-05-07
* Time: 17:10:00
*/
interface Lotto_Widget_Interface_Title
{
    /**
     * Value for div option in title_container select.
     */
    const TITLE_CONTAINER_H2 = 1;

    /**
     * Value for div option in title_container select.
     */
    const TITLE_CONTAINER_DIV = 2;
}