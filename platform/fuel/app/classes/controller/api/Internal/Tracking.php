<?php

use Abstracts\Controllers\Internal\AbstractPublicController;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Helpers\SanitizerHelper;
use Repositories\LotteryRepository;
use Repositories\Orm\WhitelabelUserRepository;

class Controller_Api_Internal_Tracking extends AbstractPublicController
{
    private Helpers_Aff_Refreader $helpersAffRefreader;
    private WhitelabelUserRepository $whitelabelUserRepository;
    private LotteryRepository $lotteryRepository;

    public function before()
    {
        parent::before();
        $this->helpersAffRefreader = Container::get(Helpers_Aff_Refreader::class);
        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $this->lotteryRepository = Container::get(LotteryRepository::class);
    }

    public function post_run(): Response
    {
        $widget = SanitizerHelper::sanitizeString(Input::get('widget') ?? '');
        $ref = SanitizerHelper::sanitizeString(Input::get('ref') ?? '');
        $whitelabel = Container::get('whitelabel');
        $pathname = Input::post('pathname', '');
        $shouldTriggerUserViewItem = Input::post('shouldTriggerUserViewItem', false);

        if (empty($whitelabel)) {
            return $this->returnResponse([]);
        }

        $user = $this->whitelabelUserRepository->getUserFromSession();
        $isUserNotLogged = empty($user);
        $isUser = !$isUserNotLogged;

        $isNotWidgetRoute = !$widget;
        $shouldProcessAff = $isNotWidgetRoute && $ref !== null && $isUserNotLogged && empty($redirect);
        if ($shouldProcessAff) {
            $redirect = (new Forms_Wordpress_Ref($whitelabel->to_array()))->process_form($pathname);
        }

        $isRefValid = $this->helpersAffRefreader->isRefValid();
        $refToken = $this->helpersAffRefreader->get_ref_token();
        if ($isRefValid && $isUserNotLogged) {
            $aff = Model_Whitelabel_Aff::fetch_aff($whitelabel->id, $refToken);
        }

        if ($isUser) {
            // validate lead. NOTE: token can be null,
            $this->helpersAffRefreader->validateLead($whitelabel->id, $refToken, $user->to_array());
            $aff = Model_Whitelabel_User_Aff::get_data_for_aff_links($user->to_array());
        }

        if (!empty($aff)) {
            if (!empty($whitelabel->analytics)) {
                Forms_Wordpress_Pixels_Gtag::set_affiliate($aff);
            }
            if (!empty($whitelabel->fb_pixel)) {
                Forms_Wordpress_Pixels_Facebook::set_affiliate($aff);
            }
        }

        /**
         *  We want to trigger first,
         * because Forms_Wordpress_Pixels_(Gtag/Facebook)::generate_code will be able to use it
         */
        /** Removed direct Facebook Pixel call; the event is now sent via GTM */
//        if ($shouldTriggerUserViewItem) {
//            $lotterySlug = SanitizerHelper::sanitizeSlug(Input::post('lotterySlug'));
//            $pageName = SanitizerHelper::sanitizeSlug(Input::post('pageName'));
//            $userId = !empty($user) ? $user->id : null;
//
//            $lottery = $this->lotteryRepository->findOneBySlug($lotterySlug);
//            $itemId = $whitelabel['prefix'] . '_' . Lotto_Helper::get_lottery_short_name($lottery) . '_TICKET';
//            $lotteryName = $lottery['name'];
//
//            $price = Helpers_Lottery::getPricing($lottery);
//            $currency = Helpers_Currency::getUserCurrencyTable();
//            $currencyCode = $currency['code'];
//
//            Event::register('user_view_item', 'Events_User_Item_View::handle');
//            Event::trigger('user_view_item', [
//                'whitelabel_id' => $whitelabel['id'],
//                'user_id' => $userId,
//                'plugin_data' => [
//                    'items' => [
//                        [
//                            'id' => $itemId,
//                            'name' => $lotteryName,
//                            'list_name' => $pageName,
//                            'quantity' => 1,
//                            'price' => $price,
//                            'currency' => $currencyCode
//                        ]
//                    ]
//                ]
//            ]);
//        }

        $scriptsHtml = Forms_Wordpress_Pixels_Gtag::generate_code();
        $scriptsHtml .= Forms_Wordpress_Pixels_Facebook::generate_code();

        return $this->returnResponse([
            'redirect' => $redirect ?? '',
            'scripts' => $scriptsHtml,
            'gTag' => $whitelabel->analytics,
            'fbPixel' => $whitelabel->fb_pixel ?? '',
            'affFbPixel' => Forms_Wordpress_Pixels_Facebook::getAffFbPixel(),
            'ref' => $ref,
            'isCasinoCampaign' => (bool)SanitizerHelper::sanitizeString(Input::get('is_casino', false) ?? ''),
        ]);
    }
}
