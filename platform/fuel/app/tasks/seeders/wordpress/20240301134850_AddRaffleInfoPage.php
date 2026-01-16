<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPage;

final class AddRaffleInfoPage extends AbstractPage
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark']; 
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'slug' => 'raffle-lotteries',
			'title' => 'Raffle Information',
			'body' => '',
		],
		'ar' => [
			'slug' => 'raffle-alyansib',
			'title' => 'Raffle Alyansib',
			'body' => '',
		],
		'az' => [
			'slug' => 'raffle-lotereya',
			'title' => 'Raffle Lotereya',
			'body' => '',
		],
		'bg' => [
			'slug' => 'raffle-lotariya',
			'title' => 'Raffle Lotariya',
			'body' => '',
		],
		'bn' => [
			'slug' => 'raffle-latari',
			'title' => 'Raffle Latari',
			'body' => '',
		],
		'cs' => [
			'slug' => 'raffle-loterie',
			'title' => 'Raffle Loterie',
			'body' => '',
		],
		'da' => [
			'slug' => 'raffle-lotteri',
			'title' => 'Raffle Lotteri',
			'body' => '',
		],
		'de' => [
			'slug' => 'raffle-lotterie',
			'title' => 'Raffle Lotterie',
			'body' => '',
		],
		'el' => [
			'slug' => 'raffle-lacheia',
			'title' => 'Raffle Lacheia',
			'body' => '',
		],
		'es' => [
			'slug' => 'raffle-loterias',
			'title' => 'Raffle Loterias',
			'body' => '',
		],
		'et' => [
			'slug' => 'raffle-loteriid',
			'title' => 'Raffle Loteriid',
			'body' => '',
		],
		'fi' => [
			'slug' => 'raffle-tiedot',
			'title' => 'Raffle tiedot',
			'body' => '',
		],
		'fil' => [
			'slug' => 'raffle-loterya',
			'title' => 'Raffle Loterya',
			'body' => '',
		],
		'fr' => [
			'slug' => 'raffle-loterie',
			'title' => 'Raffle Loterie',
			'body' => '',
		],
		'ge' => [
			'slug' => 'raffle-latariebi',
			'title' => 'Raffle Latariebi',
			'body' => '',
		],
		'he' => [
			'slug' => 'raffle-lvtv-mkvvn',
			'title' => 'Raffle Lvtv Mkvvn',
			'body' => '',
		],
		'hi' => [
			'slug' => 'raffle-lotaree',
			'title' => 'Raffle Lotaree',
			'body' => '',
		],
		'hr' => [
			'slug' => 'raffle-lutrije',
			'title' => 'Raffle Lutrije',
			'body' => '',
		],
		'hu' => [
			'slug' => 'raffle-lottojatekok',
			'title' => 'Raffle Lottojatekok',
			'body' => '',
		],
		'id' => [
			'slug' => 'raffle-lotere',
			'title' => 'Raffle Lotere',
			'body' => '',
		],
		'it' => [
			'slug' => 'raffle-lotteria',
			'title' => 'Raffle Lotteria',
			'body' => '',
		],
		'ko' => [
			'slug' => 'raffle-boggwon',
			'title' => 'Raffle Boggwon',
			'body' => '',
		],
		'lt' => [
			'slug' => 'raffle-loterija',
			'title' => 'Raffle Loterija',
			'body' => '',
		],
		'lv' => [
			'slug' => 'raffle-loterijas',
			'title' => 'Raffle Loterijas',
			'body' => '',
		],
		'mk' => [
			'slug' => 'raffle-lotarii',
			'title' => 'Raffle Lotarii',
			'body' => '',
		],
		'nl' => [
			'slug' => 'raffle-lotterijen',
			'title' => 'Raffle Lotterijen',
			'body' => '',
		],
		'pl' => [
			'slug' => 'raffle-loterie',
			'title' => 'Raffle Loterie',
			'body' => '',
		],
		'pt' => [
			'slug' => 'raffle-loterias',
			'title' => 'Raffle Loterias',
			'body' => '',
		],
		'ro' => [
			'slug' => 'raffle-loterii',
			'title' => 'Raffle Loterii',
			'body' => '',
		],
		'ru' => [
			'slug' => 'raffle-loterei',
			'title' => 'Raffle Loterei',
			'body' => '',
		],
		'sk' => [
			'slug' => 'raffle-loterie',
			'title' => 'Raffle Loterie',
			'body' => '',
		],
		'sl' => [
			'slug' => 'raffle-loterije',
			'title' => 'Raffle Loterije',
			'body' => '',
		],
		'sq' => [
			'slug' => 'raffle-lotarite',
			'title' => 'Raffle Lotarite',
			'body' => '',
		],
		'sr' => [
			'slug' => 'raffle-lutrija',
			'title' => 'Raffle Lutrija',
			'body' => '',
		],
		'sv' => [
			'slug' => 'raffle-lotteri',
			'title' => 'Raffle Lotteri',
			'body' => '',
		],
		'th' => [
			'slug' => 'raffle-lxttexri',
			'title' => 'Raffle Lxttexri',
			'body' => '',
		],
		'tr' => [
			'slug' => 'raffle-loto',
			'title' => 'Raffle Loto',
			'body' => '',
		],
		'uk' => [
			'slug' => 'raffle-loterey',
			'title' => 'Raffle Loterey',
			'body' => '',
		],
		'vi' => [
			'slug' => 'raffle-xo-so',
			'title' => 'Raffle Xo So',
			'body' => '',
		],
		'zh' => [
			'slug' => 'raffle-caipiao',
			'title' => 'Raffle Caipiao',
			'body' => '',
		],
	];

}
