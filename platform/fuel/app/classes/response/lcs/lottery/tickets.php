<?php


/**
 * Class Response_Lcs_Lottery_Tickets
 * @property-read array $tickets
 */
class Response_Lcs_Lottery_Tickets extends Response_Base
{
    protected $validator_class = Validator_Lcs_Tickets::class;
}