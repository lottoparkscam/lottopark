<?php

/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.8
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2016 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * The Welcome Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 *
 * @package  app
 * @extends  Controller
 */
class Controller_Index extends Controller
{
    /**
     * The 404 action for the application.
     *
     * @access  public
     * @return  Response
     */
    public function action_404()
    {
        $view = View::forge('index/404');
        $view->set("title", _("404 Not Found"));
        $view->set("content", _("The requested URL couldn't be found."));
        $view->set("domain", Lotto_Helper::getWhitelabelDomainFromUrl());
        $view->set("logo_url", 'https://' . Lotto_Helper::getWhitelabelDomainFromUrl() . '/wp-content/maintenance-logo.png');
        return Response::forge($view, 404);
    }
}
