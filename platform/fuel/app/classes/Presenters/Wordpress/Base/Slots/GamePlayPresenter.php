<?php

namespace Presenters\Wordpress\Base\Slots;

use Container;
use Fuel\Core\Input;
use Helper_Route;
use Helpers\SlotHelper;
use Helpers\UrlHelper;
use Lotto_Platform;
use Presenters\Wordpress\AbstractWordpressPresenter;
use Models\Whitelabel;
use Repositories\SlotGameRepository;
use Repositories\SlotProviderRepository;
use Services\Api\Slots\FindProviderService;
use Services\Api\Slots\SlotegratorInitGameService;

/**
 * Presenter for:
 * - template: /wordpress/wp-content/themes/base/template-casino-play.php
 * - view: /wordpress/wp-content/themes/base/Slots/GamePlayView.twig
 */
final class GamePlayPresenter extends AbstractWordpressPresenter
{
    /** 
     * @var mixed $initGameService - has to be mixed because it will choose service dynamically 
     * hasInitGameServiceValidType() checks if it has good type, if not redirects user to homepage.
     * It will probably never happen.
     * After adding new slotProvider you have to add new case in this function.
     */
    private $initGameService;
    private SlotegratorInitGameService $slotegratorInitGameService;
    private FindProviderService $findProviderService;
    private SlotGameRepository $slotGameRepository;
    public SlotProviderRepository $slotProviderRepository;
    private string $providerSlug;
    private Whitelabel $whitelabel;

    public function __construct(
        SlotegratorInitGameService $slotegratorInitGameService
    ) {
        $this->slotegratorInitGameService = $slotegratorInitGameService;
        $siteDoesntHaveParamsAndHasToBeRedirected = empty(Input::get()) || empty(Input::get('game_uuid'));
        if ($siteDoesntHaveParamsAndHasToBeRedirected) {
            UrlHelper::redirectToHomepage();
        }

        $this->slotGameRepository = Container::get(SlotGameRepository::class);
        $game = $this->slotGameRepository->findOneByUuid(Input::get('game_uuid'));

        if (empty($game)) {
            UrlHelper::redirectToHomepage();
        }

        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');

        $this->whitelabel = $whitelabel;
        $allowedGameUuids = SlotHelper::getAllowedGameUuids($this->whitelabel->domain);
        $gameIsNotAllowed = is_array($allowedGameUuids) && !in_array(Input::get('game_uuid'), $allowedGameUuids);
        if ($gameIsNotAllowed) {
            UrlHelper::redirectToHomepage();
        }

        $this->slotProviderRepository = Container::get(SlotProviderRepository::class);
        $this->providerSlug = $this->slotProviderRepository->findSlotProviderSlugById($game->slotProviderId);
        $this->findProviderService = new FindProviderService($game->slotProviderId, $this->slotProviderRepository);
        $className = $this->findProviderService->initClass('init');
        $this->initGameService = Container::get($className);
        $this->hasInitGameServiceValidType();
    }

    private function hasInitGameServiceValidType(): void
    {
        switch ($this->providerSlug) {
            case "slotegrator":
                $initHasWrongType = !$this->initGameService instanceof $this->slotegratorInitGameService;
                break;
            default:
                $initHasWrongType = true;
        }

        if ($initHasWrongType) {
            UrlHelper::redirectToHomepage();
        }
    }

    /** This if is added to avoid calling api. When limit is reached, it will not start anyway */
    public function view(): string
    {
        if ($this->initGameService->isLimitReached) {
            $data = [
                'isLimitReached' => true
            ];
            return $this->forge($data);
        }

        $isLobbySelect = Lotto_Platform::is_page(Helper_Route::CASINO_LOBBY, $this->whitelabel->domain);
        $data = [
            'gameUrl' => $this->getInitUrl(),
            'casinoPageUrl' => lotto_platform_get_permalink_by_slug('/'),
            'isLobby' => $isLobbySelect,
            'type' => $isLobbySelect ? 'lobby' : 'game'
        ];

        $data = array_merge($data, SlotHelper::getModeSwitchData());

        return $this->forge($data);
    }

    private function getInitUrl(): string
    {
        $mode = Input::get('mode');
        $isDemo = !empty($mode) && $mode === 'demo';

        if ($isDemo) {
            return $this->initGameService->initDemo();
        }

        return $this->initGameService->init();
    }
}
