<?php

use Abstracts\Controllers\Internal\AbstractPublicController;
use Fuel\Core\Input;
use Fuel\Core\Response;
use Helpers\SanitizerHelper;
use Helpers\UserHelper;
use Repositories\WhitelabelLotteryDrawRepository;
use Repositories\WhitelabelUserTicketRepository;

class Controller_Api_Internal_Draw extends AbstractPublicController
{
    // Do not use to expose any other data than lottery draw

    private WhitelabelUserTicketRepository $whitelabelUserTicketRepository;
    private WhitelabelLotteryDrawRepository $whitelabelLotteryDrawRepository;

    public function before()
    {
        parent::before();
        $this->whitelabelUserTicketRepository = Container::get(WhitelabelUserTicketRepository::class);
        $this->whitelabelLotteryDrawRepository = Container::get(WhitelabelLotteryDrawRepository::class);
    }

    public function get_by_ticket(): Response
    {
        try {
            $ticketToken = (int)SanitizerHelper::sanitizeString(Input::get('ticketToken'));
            $user = UserHelper::getUser();
            $isUserNotLogged = empty($user);
            if ($isUserNotLogged) {
                return $this->returnResponse([]);
            }
            $ticket = $this->whitelabelUserTicketRepository->getOneByTokenAndWhitelabelId($ticketToken, $user->whitelabel->id);
            $ticketNotBelongToUser = $ticket->whitelabel_user_id !== $user->id;
            if ($ticketNotBelongToUser) {
                return $this->returnResponse([]);
            }
            $lotteryDraw = $this->whitelabelLotteryDrawRepository->getNumbersByLotteryIdAndDrawDate($ticket->lottery_id, $ticket->draw_date);
        } catch (Throwable) {
            //not supported, used to stop error when user change ticket Token
            return $this->returnResponse([]);
        }
        if ($ticket->status === Helpers_General::TICKET_PAYOUT_PENDING) {
            return $this->returnResponse([]);
        }

        return $this->returnResponse(['numbers' => $lotteryDraw->numbers, 'status' => $ticket->status]);
    }
}
