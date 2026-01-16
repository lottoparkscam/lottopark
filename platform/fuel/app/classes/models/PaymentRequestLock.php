<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Carbon\Carbon;
use DateTime;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $whitelabel_id
 * @property int $whitelabel_user_id
 * @property int $requests_count
 * @property int $payment_method_id
 *
 * @property DateTime|Carbon $first_request_date
 *
 * @property BelongsTo|Whitelabel $whitelabel
 * @property BelongsTo|WhitelabelUser $whitelabel_user
 * @property BelongsTo|PaymentMethod $payment_method
 */
class PaymentRequestLock extends AbstractOrmModel
{
	protected static string $_table_name = 'payment_request_lock';

	protected static array $_properties = [
		'id',
		'whitelabel_id',
		'whitelabel_user_id',
		'requests_count' => ['default' => 0],
		'payment_method_id',

		'first_request_date',
	];

	protected $casts = [
		'id' => self::CAST_INT,
		'whitelabel_id' => self::CAST_INT,
		'whitelabel_user_id' => self::CAST_INT,
		'requests_count' => self::CAST_INT,
		'payment_method_id' => self::CAST_INT,

		'first_request_date' => self::CAST_CARBON,
	];

	protected array $relations = [
		PaymentMethod::class => self::BELONGS_TO,
		Whitelabel::class => self::BELONGS_TO,
		WhitelabelUser::class => self::BELONGS_TO,
	];

	protected array $timezones = [
		'first_request_date' => 'UTC'
	];

	// It is very important! Do not remove this variables!
	protected static array $_belongs_to = [];
	protected static array $_has_one = [];
	protected static array $_has_many = [];
}