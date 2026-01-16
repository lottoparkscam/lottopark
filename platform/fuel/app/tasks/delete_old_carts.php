<?php

namespace Fuel\Tasks;

use Carbon\Carbon;
use Container;
use Fuel\Core\File;
use Repositories\CartRepository;
use Throwable;
use Services\Logs\FileLoggerService;
use Traits\Logs\LogTrait;
use Wrappers\Decorators\ConfigContract;

final class Delete_Old_Carts
{
    public const INTERVAL_IN_DAYS = 14;
    private CartRepository $cartRepository;

    public function __construct()
    {
        $this->cartRepository = Container::get(CartRepository::class);
    }

    public function run(): void
    {
        $olderThanDate = Carbon::now()->subDays(self::INTERVAL_IN_DAYS)->format('Y-m-d');
        $this->cartRepository->deleteOldCarts($olderThanDate);

    }
}
