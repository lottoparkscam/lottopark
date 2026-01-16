<?php

namespace Modules\Payments;

interface CustomOptionsAwareContract
{
    /**
     * Provides in some abstraction way, a customizable options (in this case from Transaction_Model->data_json).
     * @return array
     */
    public function getOptions(): array;

    /**
     * At this moment it will be mostly Transaction_Model->prefixed_token.
     * @return string
     */
    public function getOrderId(): string;

    public function getAdditionalData(): array;
}
