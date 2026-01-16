<?php

use Fuel\Core\Input;
use Fuel\Core\Session;
use Fuel\Core\Validation;
use Helpers_General;
use Helpers\UrlHelper;
use Models\Whitelabel;
use Repositories\Aff\WhitelabelAffRepository;
use Services\AffService;
use Services\Plugin\RegisterService;

/**
 * @deprecated
 */
class Forms_Wordpress_Ref extends Forms_Main
{

    private array $whitelabel = [];
    private AffService $affService;
    private WhitelabelAffRepository $whitelabelAffRepository;

    public function __construct(array $whitelabel = [])
    {
        $this->whitelabel = $whitelabel;
        $this->affService = Container::get(AffService::class);
        $this->whitelabelAffRepository = Container::get(WhitelabelAffRepository::class);
    }

    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }

    public function isLottoPark(array $whitelabel): bool
    {
        return $whitelabel['theme'] === Whitelabel::LOTTOPARK_THEME;
    }

    protected function validate_form(): Validation
    {
        $val = Validation::forge("ref");
        
        $val->add("ref", "")
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('exact_length', 10)
            ->add_rule('valid_string', ['alpha', 'numeric']);
        
        return $val;
    }

    public function process_form(string $pathname = ''): ?string
    {
        $val = $this->validate_form();
        $whitelabel = $this->get_whitelabel();
        $inputToken = mb_strtolower(Input::get("ref"));

        if ($val->run(Input::get())) {
            $aff = $this->whitelabelAffRepository->findAffiliateByTokenOrSubToken($whitelabel, $inputToken);

            if (!empty($aff)) {
                Session::delete("medium");
                Session::delete("campaign");
                Session::delete("content");

                if (!empty(Input::get("medium"))) {
                    $linkMedium = new Forms_Wordpress_Link_Medium();
                    $linkMedium->process_form();
                }

                if (!empty(Input::get("campaign"))) {
                    $linkCampaign = new Forms_Wordpress_Link_Campaign();
                    $linkCampaign->process_form();
                }

                if (!empty(Input::get("content"))) {
                    $linkContent = new Forms_Wordpress_Link_Content();
                    $linkContent->process_form();
                }

                $btag = Input::get('btag');
                if (!empty($btag)) {
                    $validation = Validation::forge('btag');
                    $validation
                        ->add('btag')
                        ->add_rule('trim')
                        ->add_rule('stripslashes')
                        ->add_rule('required')
                        ->add_rule('min_length', 1)
                        ->add_rule('max_length', 500)
                        ->add_rule('valid_string', ['alpha', 'numeric']);

                    $isValid = $validation->run(Input::get());
                    if ($isValid) {
                        /** @var Whitelabel $whitelabelOrm */
                        $whitelabelOrm = Whitelabel::find($whitelabel['id']); // TODO: This should be removed if $whitelabel turn into Whitelabel
						$this->affService->savePropertyToCookie($whitelabelOrm, 'btag', $btag);
                    }
                }

                $externalAffId = Input::get(RegisterService::EXTERNAL_AFF_ID_NAME);
                $externalAffIdExists = !empty($externalAffId);
                if ($externalAffIdExists) {
                    $validation = Validation::forge(RegisterService::EXTERNAL_AFF_ID_NAME);
                    $validation
                        ->add(RegisterService::EXTERNAL_AFF_ID_NAME)
                        ->add_rule('trim')
                        ->add_rule('required')
                        ->add_rule('min_length', 2)
                        ->add_rule('max_length', 5)
                        ->add_rule('valid_string', ['numeric']);

                    $isValid = $validation->run(Input::get());
                    if ($isValid) {
                        $this->affService->savePropertyToCookie(Container::get('whitelabel'), RegisterService::EXTERNAL_AFF_ID_NAME, $externalAffId);
                    }
                }

                // create ref reader
                $refReader = Container::get(Helpers_Aff_Refreader::class);
                $isValidRef = true;

                Lotto_Helper::aff_count_click($aff);
                if (!$refReader->isRefValid()) {
                    $isValidRef = false;
                    Lotto_Helper::aff_count_click($aff, true); // unique
                }

                // it's a sub aff automatically assigned to aff account
                $isAutoSubAff = $aff['sub_affiliate_token'] === $inputToken;
                $affOrSubAffToken = $isAutoSubAff ? $aff['sub_affiliate_token'] : $aff['token'];

                $isBannerWithoutRedirect = Input::get("banner") == null || Input::get("lottery") == null;
                $isCasino = (bool) Input::get('is_casino', false);

                if ($this->isLottoPark($whitelabel)) {
                    $lottoPark = Whitelabel::find($whitelabel['id']);
                    switch ($inputToken) {
                        case Helpers_General::REF_TAG_MARKETING:
                            $tagMarketingClickId = Input::get('$transaction_id');
                            if (!empty($tagMarketingClickId)) {
                                $this->affService->saveTagMarketingData($tagMarketingClickId, $lottoPark);
                            }
                            break;
                        case Helpers_General::REF_DIGITAL_HUB:
                            $digitalHubClickId = Input::get('sub1');
                            if (!empty($digitalHubClickId)) {
                                $this->affService->saveDigitalHubData($digitalHubClickId, $lottoPark);
                            }
                            break;
                        case Helpers_General::REF_TIBOLARIO:
                            $tibolarioClickId = Input::get('clickid');
                            if (!empty($tibolarioClickId)) {
                                $this->affService->saveTibolarioData($tibolarioClickId, $lottoPark);
                            }
                            break;
                        case Helpers_General::REF_LOUDING_ADS:
                            $loudingAdsClickId = Input::get('sub2');
                            if (!empty($loudingAdsClickId)) {
                                $this->affService->saveLoudingAdsData($loudingAdsClickId, $lottoPark);
                            }
                            break;
                        case Helpers_General::REF_TAGD:
                            $tagdClickId = Input::get('sub1');
                            if (!empty($tagdClickId)) {
                                $this->affService->saveTagdData($tagdClickId, $lottoPark);
                            }
                            break;
                    }
                }

                // saving the token to the session or cookies if the previous cookie has expired
                // only for aff.domain.com redirect
                if (!$isValidRef && $isAutoSubAff && $isBannerWithoutRedirect) {
                    $this->affService->saveRef($affOrSubAffToken, $whitelabel);
                    return 'https://aff.' . $whitelabel['domain'];
                }

                (new Forms_Wordpress_Click($whitelabel))->process_form($pathname); // NOTE: clickID on assumption it is always provided in pair with ref. 

                if ($isBannerWithoutRedirect) {
                    $homeUrlWithoutLanguage = UrlHelper::getHomeUrlWithoutLanguage('/' . $pathname);
                    return $isCasino ? UrlHelper::changeAbsoluteUrlToCasinoUrl($homeUrlWithoutLanguage, true) : $homeUrlWithoutLanguage;
                }
            }
        }

        return null;
    }
}
