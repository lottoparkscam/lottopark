<?php

namespace Presenters\Wordpress\Base\Auth;

use Helpers\SocialMediaConnect\PresenterHelper;
use Models\Whitelabel;
use Fuel\Core\Response;
use Container;
use Fuel\Core\Input;
use Services\SocialMediaConnect\PresenterService;
use Wrappers\Decorators\ConfigContract;
use Fuel\Core\Security;
use Fuel\Core\Request;
use Helpers\CaptchaHelper;
use Helpers\FlashMessageHelper;
use Helpers\WhitelabelHelper;
use Presenters\Wordpress\AbstractWordpressPresenter;

/** 
 * Presenter for: wordpress/wp-content/themes/base/Auth/LoginView.twig
 */
class LoginPresenter extends AbstractWordpressPresenter
{
    private Whitelabel $whitelabel;
    private ConfigContract $configContract;

    public function __construct(ConfigContract $configContract)
    {
        $this->whitelabel = Container::get('whitelabel');
        $this->configContract = $configContract;
    }

    public function view(): string
    {
        if (!$this->whitelabel->canUserLoginViaSite) {
            Response::redirect(lotto_platform_home_url());
        }

        $loginField = WhitelabelHelper::getLoginField();
        $isLoginByEmail = WhitelabelHelper::isLoginByEmail();
        $socialConnectPresenter = Container::get(PresenterService::class);
        $lastError = FlashMessageHelper::getLast();
        $errors = empty($lastError) ? [] : [$lastError];
        $data = [
            'url' => [
                'action' => Request::forge('wordpress/check_login')->execute(),
                'signUp' => lotto_platform_get_permalink_by_slug('signup'),
                'lostPassword' => lotto_platform_get_permalink_by_slug('lostpassword')
            ],
            'show_captcha' => true,
            'errors' => $errors,
            'csrf' => [
                'key' => $this->configContract->get('security.csrf_token_key'),
                'value' => Security::fetch_token()
            ],
            'displayErrors' => !empty($errors),
            'errorClass' => !empty($errors['login']) ? ' has-errors' : '',
            'loginField' => $loginField,
            'currentLogin' => Input::post("login.$loginField", ''),
            'placeholder' => $isLoginByEmail ? _('Your e-mail address') : _('Your login'),
            'loginInputType' => $isLoginByEmail  ? WhitelabelHelper::LOGIN_FIELD_EMAIL : 'text',
            'captcha' => CaptchaHelper::getCaptcha(),
            'socialSection' => $socialConnectPresenter->generateSocialButtonsView(),
        ];

        return $this->forge($data);
    }
}
