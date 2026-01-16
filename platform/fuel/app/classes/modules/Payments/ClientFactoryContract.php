<?php

namespace Modules\Payments;

use GuzzleHttp\ClientInterface;

/**
 * Interface ClientFactoryContract
 *
 * Factories from this contract should create, generic re-usable
 * clients (Guzzle) in given Payment scope. For example we can have multiple clients for Jeton scope:
 * - requesting checkout url
 * - checking payment status
 * - canceling payment
 *
 * And all of them needs commonly used credentials, content type headers etc.
 * This is a good place to merge hardcoded constant values with CustomOptionsAwareContract
 * which simply provide customizable options from the outside of this module
 * (it might be Orm_Transaction_Model, no ORM model or anything else).
 */
interface ClientFactoryContract
{
    public function create(CustomOptionsAwareContract $order, array $payload = []): ClientInterface;
}
