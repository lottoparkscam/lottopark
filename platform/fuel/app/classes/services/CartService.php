<?php

namespace Services;

use Carbon\Carbon;
use Exception;
use Models\Cart;
use Repositories\CartRepository;

class CartService
{
    private CartRepository $cartRepository;

    public function __construct(
        CartRepository $cartRepository,
    ) {
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param int $userId
     * @param array $ticketLines
     * @return Cart
     * @throws Exception
     */
    public function createOrUpdateCart(int $userId, array $ticketLines): Cart
    {
        $cart = $this->cartRepository->findCartByUserId($userId);

        if (!$cart) {
            $cart = new Cart([
                'whitelabel_user_id' => $userId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        } else {
            $cart->updated_at = Carbon::now();
        }

        $this->cartRepository->save($cart);

        $this->cartRepository->saveCartTickets($cart->id, $ticketLines);

        return $cart;
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getCart(int $userId): array
    {
        return $this->cartRepository->getCart($userId);
    }

    /**
     * @param int $userId
     * @throws Exception
     */
    public function deleteCart(int $userId): void
    {
        $cart = $this->cartRepository->findCartByUserId($userId);

        if ($cart) {
            $this->cartRepository->deleteCart($cart->id);
        }
    }
}