<?php
use Fuel\Core\View;

/**
 * Base class for wordpress presenters.
 * @deprecated use AbstractWordpressPresenter instead
 */
abstract class Presenter_Wordpress_Presenter extends Presenter_Presenter
{

	private static $base_path;

	/**
	 * Factory for fetching the Presenter
	 *
	 * @param   string  $presenter    Presenter classname without View_ prefix or full classname
	 * @param   string  $method       Method to execute
	 * @param   bool    $auto_filter  Auto filter the view data
	 * @param   string  $view         View to associate with this presenter
	 * @return  Presenter
	 */
	public static function forge($presenter, $method = 'view', $auto_filter = null, $view = null)
	{
		// prepare path to the view
		self::$base_path = self::$base_path ?: Helpers_App::get_absolute_file_path('wordpress/wp-content/themes');

		$relative_path = substr($presenter, 9) . '.php'; // omit 'wordpress'

		return parent::forge($presenter, $method, $auto_filter, View::forge(self::$base_path . $relative_path)); // custom view injection
	}
}
