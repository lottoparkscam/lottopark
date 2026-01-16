<?php

namespace unit\classes\services;

use Carbon\Carbon;
use Models\Cart;
use Repositories\CartRepository;
use Services\CartService;
use Test_Unit;

final class CartServiceTest extends Test_Unit
{
    private CartRepository $cartRepositoryMock;
    private CartService $cartService;

    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2024, 11, 29, 11, 36, 36));

        $this->cartRepositoryMock = $this->createMock(CartRepository::class);
        $this->cartService = new CartService($this->cartRepositoryMock);
    }

    public function testCreateOrUpdateCart_NewCart(): void
    {
        $userId = 1;
        $ticketLines = [
            [
                'lottery' => 10,
                'ticket_multiplier' => 2,
                'lines' => [
                    ['numbers' => [1, 2, 3, 4, 5]],
                    ['numbers' => [6, 7, 8, 9, 10]],
                ],
            ],
        ];

        $cart = new Cart([
            'whitelabel_user_id' => $userId,
            'created_at' => Carbon::now(),
        ]);
        $cart->id = 1;

        $this->cartRepositoryMock
            ->expects($this->once())
            ->method('findCartByUserId')
            ->with($userId)
            ->willReturn(null);

        $this->cartRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function ($cart) {
                $cart->id = 1;
                return true;
            });

        $this->cartRepositoryMock
            ->expects($this->once())
            ->method('saveCartTickets')
            ->with($cart->id, $ticketLines);

        $cart = $this->cartService->createOrUpdateCart($userId, $ticketLines);

        $this->assertInstanceOf(Cart::class, $cart);
    }

    /**
     * Test updating an existing cart with ticket lines.
     */
    public function testCreateOrUpdateCart_ExistingCart(): void
    {
        $userId = 1;
        $existingCart = new Cart([
            'whitelabel_user_id' => $userId,
            'created_at' => Carbon::now()
        ]);
        $existingCart->id = 1;
        $ticketLines = [
            [
                'lottery' => 10,
                'ticket_multiplier' => 2,
                'lines' => [
                    ['numbers' => [1, 2, 3, 4, 5]],
                    ['numbers' => [6, 7, 8, 9, 10]],
                ],
            ],
        ];

        $this->cartRepositoryMock
            ->expects($this->once())
            ->method('findCartByUserId')
            ->with($userId)
            ->willReturn($existingCart);

        $this->cartRepositoryMock
            ->expects($this->once())
            ->method('saveCartTickets')
            ->with($existingCart->id, $ticketLines);

        $cart = $this->cartService->createOrUpdateCart($userId, $ticketLines);

        $this->assertSame($existingCart, $cart);
    }

    /**
     * Test getting the cart for a user.
     */
    public function testGetCart(): void
    {
        $userId = 1;
        $cartData = [
            [
                'lottery' => '10',
                'lines' => [
                    [
                        'numbers' => [1, 2, 3, 4, 5],
                        'bnumbers' => [6, 7],
                    ],
                    [
                        'numbers' => [8, 9, 10, 11, 12],
                        'bnumbers' => [13, 14],
                    ],
                ],
                'ticket_multiplier' => 2,
                'numbers_per_line' => 5,
                'multidraw' => [1, 2, 3],
            ],
            [
                'lottery' => '20',
                'lines' => [
                    [
                        'numbers' => [15, 16, 17, 18, 19],
                        'bnumbers' => [20, 21],
                    ],
                ],
                'ticket_multiplier' => 1,
                'numbers_per_line' => 5,
                'multidraw' => null,
            ],
        ];

        $this->cartRepositoryMock
            ->expects($this->once())
            ->method('getCart')
            ->with($userId)
            ->willReturn($cartData);

        $result = $this->cartService->getCart($userId);

        $this->assertEquals($cartData, $result);
    }

    /**
     * Test deleting a cart.
     */
    public function testDeleteCart_ExistingCart(): void
    {
        $userId = 1;
        $cart = new Cart([
            'whitelabel_user_id' => $userId,
            'created_at' => Carbon::now()
        ]);
        $cart->id = 1;

        $this->cartRepositoryMock
            ->expects($this->once())
            ->method('findCartByUserId')
            ->with($userId)
            ->willReturn($cart);

        $this->cartRepositoryMock
            ->expects($this->once())
            ->method('deleteCart')
            ->with($cart->id);

        $this->cartService->deleteCart($userId);
    }

    /**
     * Test deleting a cart that doesn't exist.
     */
    public function testDeleteCart_NonexistentCart(): void
    {
        $userId = 1;

        $this->cartRepositoryMock
            ->expects($this->once())
            ->method('findCartByUserId')
            ->with($userId)
            ->willReturn(null);
        $this->cartService->deleteCart($userId);
    }
}
