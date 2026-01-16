<?php

namespace Repositories;

use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Fuel\Core\DB;
use Models\Cart;
use Models\CartTicket;
use Models\CartTicketLine;
use Repositories\Orm\AbstractRepository;
use Exception;

class CartRepository extends AbstractRepository
{
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }

    /**
     * @param int $userId
     * @return Cart|null
     */
    public function findCartByUserId(int $userId): ?Cart
    {
        $this->pushCriterias(
            [
                new Model_Orm_Criteria_Where('whitelabel_user_id', $userId),
            ]
        );

        return $this->findOne();
    }

    /**
     * @param int $cartId
     * @param array $lines
     */
    public function saveCartTickets(int $cartId, array $lines): void
    {
        DB::start_transaction();
        try {
           $this->deleteCartTicket($cartId);

            foreach ($lines as $line) {
                $cartTicketId = DB::insert(CartTicket::get_table_name())
                    ->set([
                        'cart_id' => $cartId,
                        'lottery_id' => $line['lottery'],
                        'ticket_multiplier' => $line['ticket_multiplier'] ?? null,
                        'numbers_per_line' => $line['numbers_per_line'] ?? null,
                        'multidraw' => isset($line['multidraw']) ? json_encode($line['multidraw']) : null,
                    ])
                    ->execute()[0];

                foreach ($line['lines'] as $item) {
                    DB::insert(CartTicketLine::get_table_name())
                        ->set([
                            'cart_ticket_id' => $cartTicketId,
                            'numbers' => json_encode($item['numbers']),
                            'bnumbers' => json_encode($item['bnumbers']),
                        ])
                        ->execute();
                }
            }

            DB::commit_transaction();
        } catch (Exception $e) {
            DB::rollback_transaction();
            throw $e;
        }
    }

    public function deleteCart(int $cartId): void
    {
        DB::start_transaction();
        try {
           $this->deleteCartTicket($cartId);

            DB::delete(Cart::get_table_name())
                ->where('id', '=', $cartId)
                ->execute();

            DB::commit_transaction();
        } catch (Exception $e) {
            DB::rollback_transaction();
            throw $e;
        }
    }

    public function deleteCartTicket(int $cartId): void
    {
        DB::delete(CartTicketLine::get_table_name())
            ->where('cart_ticket_id', 'IN', DB::select('id')
                ->from(CartTicket::get_table_name())
                ->where('cart_id', '=', $cartId)
            )
            ->execute();

        DB::delete(CartTicket::get_table_name())
            ->where('cart_id', '=', $cartId)
            ->execute();
    }

    public function getCart(int $userId)
    {
        $cart = $this->findCartByUserId($userId);

        if (!$cart) {
            return [];
        }

        $query = DB::select(
            'cart_tickets.lottery_id',
            'cart_tickets.ticket_multiplier',
            'cart_tickets.numbers_per_line',
            'cart_tickets.multidraw',
            'cart_ticket_lines.numbers',
            'cart_ticket_lines.bnumbers'
        )
            ->from(CartTicket::get_table_name())
            ->join(CartTicketLine::get_table_name(), 'INNER')
            ->on('cart_tickets.id', '=', 'cart_ticket_lines.cart_ticket_id')
            ->where('cart_tickets.cart_id', '=', $cart->id)
            ->execute();

        $result = [];
        foreach ($query as $row) {

            $lotteryKey = array_search($row['lottery_id'], array_column($result, 'lottery'));

            if ($lotteryKey === false) {
                $result[] = [
                    'lottery' => (string)$row['lottery_id'],
                    'lines' => [],
                    'ticket_multiplier' => isset($row['ticket_multiplier']) ? $row['ticket_multiplier'] : null,
                    'numbers_per_line' => isset($row['numbers_per_line']) ? $row['numbers_per_line'] : null,
                    'multidraw' => isset($row['multidraw']) ? json_decode($row['multidraw'], true) : null,
                ];
                $lotteryKey = array_key_last($result);
            }

            $result[$lotteryKey]['lines'][] = [
                'numbers' => json_decode($row['numbers'], true),
                'bnumbers' => json_decode($row['bnumbers'], true),
            ];
        }

        return $result;
    }

    public function deleteOldCarts(string $olderThanDate): void
    {
        DB::start_transaction();
        try {
            DB::delete(CartTicketLine::get_table_name())
                ->where('cart_ticket_id', 'IN', DB::select('id')
                    ->from(CartTicket::get_table_name())
                    ->where('cart_id', 'IN', DB::select('id')
                        ->from(Cart::get_table_name())
                        ->where('updated_at', '<', $olderThanDate)
                    )
                )
                ->execute();

            DB::delete(CartTicket::get_table_name())
                ->where('cart_id', 'IN', DB::select('id')
                    ->from(Cart::get_table_name())
                    ->where('updated_at', '<', $olderThanDate)
                )
                ->execute();

            DB::delete(Cart::get_table_name())
                ->where('updated_at', '<', $olderThanDate)
                ->execute();

            DB::commit_transaction();
        } catch (Exception $e) {
            DB::rollback_transaction();
            throw $e;
        }
    }
}
