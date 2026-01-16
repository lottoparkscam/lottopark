<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractNavigation;
use Models\Lottery;

final class KenoNewYorkNavButton extends AbstractNavigation
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark.com']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => 'Keno New York',
		'pl' => 'Keno New York',
		'az' => 'Keno Nyu-York',
		'cs' => 'Keno New York',
		'da' => 'Keno New York',
		'de' => 'Keno New York',
		'et' => 'Keno New York',
		'es' => 'Keno Nueva York',
		'fr' => 'Keno New York',
		'hr' => 'Keno New York',
		'id' => 'Keno New York',
		'it' => 'Keno New York',
		'lv' => 'Ņujorka Keno',
		'lt' => 'Niujorkas Keno',
		'hu' => 'New York Keno',
		'mk' => 'Њујорк Кено',
		'nl' => 'New York Keno',
		'pt' => 'Keno Nova York',
		'ro' => 'Keno New York',
		'sq' => 'Keno Nju Jork',
		'sk' => 'New York Keno',
		'sl' => 'New York Keno',
		'sr' => 'Njujork Keno',
		'sv' => 'New York Keno',
		'fil' => 'New York Keno',
		'vi' => 'New York Keno',
		'tr' => 'New York Keno',
		'uk' => 'Кено Нью-Йорк',
		'el' => 'Νέα Υόρκη Κίνο',
		'bg' => 'Ню Йорк Кено',
		'ru' => 'Кено Нью-Йорк',
		'ge' => 'კენო ნიუ იორკი',
		'ar' => 'يانصيب الكينو نيويوركي',
		'hi' => 'न्यूयॉर्क केनो',
		'bn' => 'নিউ ইয়র্ক কেনো',
		'th' => 'Keno นิวยอร์ก',
		'ko' => '뉴욕 키노',
		'zh' => '纽约基诺',
		'fa' => 'کینو نیویورک',
		'fi' => 'New York Keno',
		'ja' => 'ニューヨーク キノ',
		'he' => 'Keno ניו יורק',
	]; 
	protected const SLUG_FOR_LINK = 'play/' . Lottery::KENO_NEW_YORK_SLUG; 
}
