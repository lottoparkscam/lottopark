<?php

use Fuel\Core\Response;
use Repositories\Orm\WhitelabelUserRepository;
use Services\Api\Balance\BalanceChangeService;
use Services\Api\Controller;
use Services\Api\Reply;
use Validators\BalanceChargeValidator;

class Controller_Api_Users_Balance_Charge extends Controller
{
    private BalanceChangeService $changeService;

    private BalanceChargeValidator $balanceChargeValidator;

    private WhitelabelUserRepository $whitelabelUserRepository;

    public function __construct(\Request $request)
    {
        $this->changeService = Container::get(BalanceChangeService::class);
        $this->balanceChargeValidator = Container::get(BalanceChargeValidator::class);
        $this->whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);

        parent::__construct($request);
    }

    /**
     * @OA\Patch(
     *     path="/users/balance/charge",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 required={"user_email", "user_login", "currency_code", "amount"},
     *                 @OA\Property(
     *                     property="user_email",
     *                     type="string",
     *                     description="When your users identify by email use user_email in other case use user_login."
     *                 ),
     *                 @OA\Property(
     *                     property="user_login",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="currency_code",
     *                     type="string",
     *                     description="Three letters ISO currency code.",
     *                     example="EUR"
     *                 ),
     *                 @OA\Property(
     *                     property="amount",
     *                     type="float",
     *                     description="Value that will be added to the user's balance"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="",
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="status",
     *                          type="string",
     *                          example="success"
     *                      ),
     *                      @OA\Property(
     *                          property="data",
     *                          type="string",
     *                          example="Balance has been changed"
     *                      )
     *                  )
     *              )
     *          )
     * )
     * @throws Exception
     */
    public function patch_index(): Response
    {
        set_time_limit(25);

        $this->balanceChargeValidator->setBuildArguments($this->whitelabel);
        $this->balanceChargeValidator->setExtraCheckArguments($this->whitelabel);
        $isNotValid = !$this->balanceChargeValidator->isValid();

        if ($isNotValid) {
            return $this->returnResponse(
                $this->balanceChargeValidator->getErrors(),
                Reply::BAD_REQUEST
            );
        }

        [$amount, $login, $email, $currencyCode] = $this->balanceChargeValidator->getProperties([
            'amount', 'user_login', 'user_email', 'currency_code'
        ]);

        $whitelabelUser = $this->whitelabelUserRepository->findSpecificUser(
            $login,
            $email,
            $this->whitelabel
        );

        $okResponse = $this->returnResponse(['Balance has been changed']);

        $isUserBalanceNotCharged = !$this->changeService->changeUserBalance(
            $amount,
            $currencyCode,
            $whitelabelUser
        );

        if ($isUserBalanceNotCharged) {
            $error = $this->changeService->getError();
            return $this->returnResponse(...$error);
        }

        return $okResponse;
    }
}
