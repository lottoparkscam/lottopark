<?php

use Abstracts\Controllers\Internal\AbstractPublicController;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Fuel\Core\Session;
use Helpers\MultiDrawHelper;
use Helpers\SanitizerHelper;
use Models\Whitelabel;
use Repositories\Orm\WhitelabelUserRepository;
use Repositories\WhitelabelMultiDrawOptionRepository;
use Repositories\WhitelabelRepository;
use Services\CartService;

class Controller_Api_Internal_Order extends AbstractPublicController
{
    private WhitelabelRepository $whitelabelRepository;
    private WhitelabelMultiDrawOptionRepository $whitelabelMultiDrawOptionRepository;
    private Whitelabel $whitelabel;
    private WhitelabelUserRepository $whitelabelUserRepository;
    private CartService $cartService;

    public function before()
    {
        parent::before();
        $this->whitelabelRepository = Container::get(WhitelabelRepository::class);
        $this->whitelabelMultiDrawOptionRepository = Container::get(WhitelabelMultiDrawOptionRepository::class);
        try {
            $this->whitelabel = $this->whitelabelRepository->getWhitelabelFromUrl();
        } catch (Exception $e) {
        }
        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $this->cartService = Container::get(CartService::class);
    }

    public function get_summary(): Response
    {
        if (empty($this->whitelabel)) {
            return $this->returnResponse(['error' => 'Whitelabel not found!'], 404);
        }

        return $this->returnResponse($this->getSummary());
    }

    public function get_items(): Response
    {
        if (empty($this->whitelabel)) {
            return $this->returnResponse(['error' => 'Whitelabel not found!'], 404);
        }

        $cartItems = Session::get('order');
        if (empty($cartItems)) {
            return $this->returnResponse([]);
        }

        $preparedCartItems = [];
        $lotteries = Helpers_Lottery::getLotteries();

        foreach ($cartItems as $item) {
            $lotteryHasChanged = !key_exists($item['lottery'], $lotteries['__by_id']);
            if ($lotteryHasChanged) {
                continue;
            }

            $lottery = $lotteries['__by_id'][$item['lottery']];
            $ticketMultiplier = $item['ticket_multiplier'] ?? 1;
            $pricing = (float)Helpers_Lottery::getPricing($lottery, $ticketMultiplier);
            $linesCount = !empty($item['lines']) ? count($item['lines']) : 0;
            $itemPrice = round($pricing * $linesCount, 2);

            $isMultiDrawTicket = isset($item['multidraw'][0]) &&
                (int)$item['multidraw'][0] === Helpers_General::ORDER_TICKET_MULTIDRAW;
            if ($isMultiDrawTicket) {
                $whitelabelMultiDrawOptionId = $item['multidraw'][1];
                $whitelabelMultiDrawOption = $this->whitelabelMultiDrawOptionRepository->findOneByIdAndWhitelabelId(
                    $whitelabelMultiDrawOptionId,
                    $this->whitelabel->id
                );
            }

            if (!empty($whitelabelMultiDrawOption)) {
                $itemPrice = MultiDrawHelper::calculateMultiDrawTicketPrice($whitelabelMultiDrawOption, $itemPrice);
            }

            $userCurrencyCode = Helpers_Currency::getUserCurrencyTable()['code'];
            $preparedCartItems[] = [
                'label' => sprintf(_("%s ticket"), _($lottery['name'])),
                'price' => Lotto_View::format_currency(
                    $itemPrice,
                    $userCurrencyCode,
                    true
                )
            ];
        }

        return $this->returnResponse($preparedCartItems);
    }

    public function post_deleteItem(): Response
    {
        $id = (int)SanitizerHelper::sanitizeString(Input::get('id') ?? '');
        $order = Session::get('order');

        $isNotIdInOrder = !isset($order[$id]);
        if ($isNotIdInOrder) {
            return $this->returnResponse([], 204);
        }

        array_splice($order, $id, 1);

        $userId = $this->whitelabelUserRepository->getUserFromSession()->id;

        $orderIsNotEmpty = !empty($order);
        if ($orderIsNotEmpty) {
            if ($userId) {
                $this->cartService->createOrUpdateCart($userId, $order);
            }

            Session::set('order', $order);
        } else {
            if ($userId) {
                $this->cartService->deleteCart($userId);
            }

            Session::delete('order');
        }

        return $this->returnResponse($this->getSummary());
    }

    /** @return array{sum: float, sumAfterDiscount: float, count: int} */
    private function getSummary(): array
    {
        $orderSum = Helpers_Currency::sum_order(false);
        $currencyCode = Helpers_Currency::getUserCurrencyTable()['code'];
        $formattedOrderSum = Lotto_View::format_currency($orderSum, $currencyCode, true);

        $promoCodeForm = Forms_Whitelabel_Bonuses_Promocodes_Code::get_or_create(
            $this->whitelabel,
            Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_PURCHASE
        );

        $isEnabled = !$promoCodeForm->get_is_disabled();
        $promoCode = $promoCodeForm->get_promo_code();
        $discountExists = $isEnabled && isset($promoCode) && isset($promoCode['discount_user']);
        if ($discountExists) {
            $discount = $promoCode['discount_user'];
            $sumAfterDiscount = $orderSum - $discount;
        }

        $lotteries = Helpers_Lottery::getLotteries();
        $order = Session::get("order") ?? [];
        $itemsForEnabledLotteries = array_filter($order, function ($item) use ($lotteries) {
            return isset($lotteries['__by_id'][$item['lottery']]);
        });

        return [
            'sum' => $formattedOrderSum,
            'sumAfterDiscount' => $sumAfterDiscount ?? $formattedOrderSum,
            'count' => count($itemsForEnabledLotteries)
        ];
    }
}
