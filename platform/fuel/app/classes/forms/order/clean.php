<?php

use Services\CartService;

/**
 *
 */
final class Forms_Order_Clean extends Forms_Main
{
    /**
     * Useful when globaly disabling lottery, cleans up the basket
     *
     * @param array $lotteries
     * @return void
     */
    public static function process_form(array $lotteries = null): void
    {
        $order = Session::get("order");
        $changed = false;
        $new_order = [];
        
        if (!empty($order)) {
            foreach ($order as $item) {
                if (!is_null($lotteries) && isset($item['lottery']) && isset($lotteries['__by_id'][$item['lottery']])) {
                    $new_order[] = $item;
                } else {
                    $changed = true;
                }
            }
        }
        
        if ($changed) {
            Session::set("order", $new_order);
            $cartService = Container::get(CartService::class);

            $userId = lotto_platform_user_id();
            if ($userId) {
                $cartService->createOrUpdateCart($userId, $new_order);
            }
        }
    }
}
