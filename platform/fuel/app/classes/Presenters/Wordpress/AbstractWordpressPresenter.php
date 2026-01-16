<?php

namespace Presenters\Wordpress;

use Helpers_App;
use Lotto_Helper;
use Modules\View\ViewHelper;
use Helpers\CaptchaHelper;
use Helpers\SecurityHelper;
use ReflectionClass;

/**
 * Available global variables:
 * - {{title}} - current page title
 * - {{content}} - current page title
 * - {{csrf}} - exists only if hasForm = true; ready input with csrf content
 * - {{captcha}} - exists only if hasForm = true; ready captcha
 */
abstract class AbstractWordpressPresenter
{
	protected bool $hasForm = false;
    protected bool $isCsrfEnabled = true;

	private function getGlobalVariables(array $data): array
	{
		$data['title'] = get_the_title();
		$data['content'] = get_the_content();

		if ($this->hasForm) {
            if ($this->isCsrfEnabled) {
                $data['csrf'] = SecurityHelper::getCsrfInput();
            }
			$data['captcha'] = CaptchaHelper::getCaptcha();
		}

		return $data;
	}

	protected function forge(array $data): string
	{
		$reflector = new ReflectionClass(static::class);

		$data = $this->getGlobalVariables($data);

		$pathToWWW = Helpers_App::get_absolute_file_path("");
		$pathToPresenter = $reflector->getFileName();
		$pathToThemes = Helpers_App::get_absolute_file_path("wordpress/wp-content/themes/");

		$replace = [
			"Presenter.php" => "View.twig",
			"$pathToWWW" => "",
			"Wordpress/" => "",
			"platform/fuel/app/classes/Presenters/" => "",
			"Base/" => "base/"
		];

        /**
         * Important! On local environments (Docker for Macos), the path returned by getFileName() is sometimes in lowercase letter.
         * Hence, the use of str_ireplace to ensure on any environment the views are loading properly.
         */
		$pathToView = $pathToThemes . str_ireplace(array_keys($replace), array_values($replace), $pathToPresenter);

		if (file_exists($pathToView)) {
			return do_shortcode(ViewHelper::render($pathToView, $data));
		}

		return '';
	}

    protected function getOutputFromHookByWordpressFileName(string $filename): string
    {
        // the Lotto_Helper::hook method only loads a given file and returns true/false,
        // so in order to obtain the HTML, we need to capture it using output buffering
        ob_start();
        Lotto_Helper::hook($filename);
        return ob_get_clean() ?? '';
    }

	abstract public function view(): string;
}
