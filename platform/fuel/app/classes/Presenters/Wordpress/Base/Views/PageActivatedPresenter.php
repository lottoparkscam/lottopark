<?php

namespace Presenters\Wordpress\Base\Views;

use Container;
use Fuel\Core\Session;
use Fuel\Tasks\Seeders\Wordpress\FaireumDepositAndWithdrawalInstructionsPage;
use Lotto_Settings;
use Models\Whitelabel;
use Presenters\Wordpress\AbstractWordpressPresenter;
use Helpers_General;

/**
 * Only displayed for logged in users.
 * Presenter for /wordpress/wp-content/themes/base/page-activated.php
 */
final class PageActivatedPresenter extends AbstractWordpressPresenter
{
    private Whitelabel $whitelabel;

    public function __construct()
    {
        $this->whitelabel = Container::get('whitelabel');
    }

    public function view(): string
    {
        $additionalThemeHook = $this->getOutputFromHookByWordpressFileName('page-activated');

        $data = [
            'pageMessage' => $this->getPageMessage(),
            'actionButtons' => $this->getActionButtons(),
            'additionalThemeHooks' => $additionalThemeHook,
            'pageWidget' => $this->getPageWidget(),
        ];

        return $this->forge($data);
    }

    private function getPageMessage(): string
    {
        $loggedInMessage = _('Thank you for choosing us! You have been logged in.') . "</br>";
        $whitelabelUser = Lotto_Settings::getInstance()->get('user');
        $isActivationRequired = $this->whitelabel->userActivationType === Helpers_General::ACTIVATION_TYPE_REQUIRED;

        if ($whitelabelUser['is_confirmed']) {
           return ($isActivationRequired ? $loggedInMessage : '')
               . _('Your account is now fully active.');
        }

        $isActivationNone = $this->whitelabel->userActivationType === Helpers_General::ACTIVATION_TYPE_NONE;
        if ($isActivationNone) {
            return $loggedInMessage;
        }

        return $loggedInMessage . "</br>" .
            _('To fully activate your account and get access to all functionalities, please confirm your e-mail address by following the confirmation link we have sent you to your e-mail.');

    }

    private function getActionButtons(): string
    {
        $isCartNotEmpty = !empty(Session::get('order'));
        if ($isCartNotEmpty) {
            $orderButtonUrl = lotto_platform_get_permalink_by_slug('order');
            $orderButtonText = _('Back to order');
            $buttons = <<<ELEM
        <a href="$orderButtonUrl" class="btn btn-lg btn-primary">$orderButtonText</a>
        ELEM;
        } else {
            $isFaireumCasino = $this->whitelabel->isTheme(Whitelabel::FAIREUM_THEME) && IS_CASINO;
            if ($isFaireumCasino) {
                $depositButtonUrl = lotto_platform_get_permalink_by_slug(
                    FaireumDepositAndWithdrawalInstructionsPage::SLUG
                );
                $depositButtonText = _('Deposit & Withdrawal');
            } else {
                $depositButtonUrl = lotto_platform_get_permalink_by_slug('deposit');
                $depositButtonText = _('Deposit');
            }

            $buttons = <<<ELEM
        <a href="$depositButtonUrl" class="btn btn-lg btn-secondary">$depositButtonText</a>
       ELEM;
            if (!IS_CASINO) {
                $buyTicketsButtonUrl = lotto_platform_get_permalink_by_slug('play');
                $buyTicketsButtonText = _('Buy tickets');
                $buttons .= <<<ELEM
            <a href="$buyTicketsButtonUrl" class="btn btn-lg btn-primary">$buyTicketsButtonText</a>
            ELEM;
            }
        }
        return $buttons;
    }

    private function getPageWidget(): string
    {
        if (!IS_CASINO) {
            // Hack to catch widget output into buffer, as the_widget does not return html
            ob_start();
            the_widget(
                'Lotto_Widget_List',
                array('count' => 3, 'countdown' => 2, 'display' => 2, 'type' => 2)
            );

            // Return buffered widget code
            return ob_get_clean();
        }

        return '';
    }
}
