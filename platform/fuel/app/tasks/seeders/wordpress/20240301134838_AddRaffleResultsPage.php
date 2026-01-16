<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPage;

final class AddRaffleResultsPage extends AbstractPage
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'slug' => 'raffle-results',
			'title' => 'Raffle Results',
			'body' => '', 
		],
		'ar' => [
			'slug' => 'raffle-alnatayij',
			'title' => 'Raffle Alnatayij',
			'body' => '',
		],
		'az' => [
			'slug' => 'raffle-neticeleri',
			'title' => 'Raffle Neticeleri',
			'body' => '',
		],
		'bg' => [
			'slug' => 'raffle-rezultati',
			'title' => 'Raffle Rezultati',
			'body' => '',
		],
		'bn' => [
			'slug' => 'raffle-phalaphala',
			'title' => 'Raffle Phalaphala',
			'body' => '',
		],
		'cs' => [
			'slug' => 'raffle-vysledky',
			'title' => 'Raffle Vysledky',
			'body' => '',
		],
		'da' => [
			'slug' => 'raffle-resultater',
			'title' => 'Raffle Resultater',
			'body' => '',
		],
		'de' => [
			'slug' => 'raffle-ergebnisse',
			'title' => 'Raffle Ergebnisse',
			'body' => '',
		],
		'el' => [
			'slug' => 'raffle-apotelesmata',
			'title' => 'Raffle Apotelesmata',
			'body' => '',
		],
		'es' => [
			'slug' => 'raffle-resultados',
			'title' => 'Raffle Resultados',
			'body' => '',
		],
		'et' => [
			'slug' => 'raffle-tulemused',
			'title' => 'Raffle Tulemused',
			'body' => '',
		],
		'fi' => [
			'slug' => 'raffle-pelaa',
			'title' => 'Pelaa Raffle netissä',
			'body' => '',
		],
		'fil' => [
			'slug' => 'raffle-resulta',
			'title' => 'Raffle Resulta',
			'body' => '',
		],
		'fr' => [
			'slug' => 'raffle-resultats',
			'title' => 'Raffle Resultats',
			'body' => '',
		],
		'ge' => [
			'slug' => 'raffle-shedegebi',
			'title' => 'Raffle Shedegebi',
			'body' => '',
		],
		'he' => [
			'slug' => 'raffle-myd-l-lottery',
			'title' => 'Raffle Myd L Lottery',
			'body' => '',
		],
		'hi' => [
			'slug' => 'raffle-parinaam',
			'title' => 'Raffle Parinaam',
			'body' => '',
		],
		'hr' => [
			'slug' => 'raffle-rezultati',
			'title' => 'Raffle Rezultati',
			'body' => '',
		],
		'hu' => [
			'slug' => 'raffle-eredmenyek',
			'title' => 'Raffle Eredmenyek',
			'body' => '',
		],
		'id' => [
			'slug' => 'raffle-hasil',
			'title' => 'Raffle Hasil',
			'body' => '',
		],
		'it' => [
			'slug' => 'raffle-risultati',
			'title' => 'Raffle Risultati',
			'body' => '',
		],
		'ko' => [
			'slug' => 'raffle-gyeolgwa',
			'title' => 'Raffle Gyeolgwa',
			'body' => '',
		],
		'lt' => [
			'slug' => 'raffle-rezultatai',
			'title' => 'Raffle Rezultatai',
			'body' => '',
		],
		'lv' => [
			'slug' => 'raffle-rezultati',
			'title' => 'Raffle Rezultati',
			'body' => '',
		],
		'mk' => [
			'slug' => 'raffle-rezultati',
			'title' => 'Raffle Rezultati',
			'body' => '',
		],
		'nl' => [
			'slug' => 'raffle-resultaten',
			'title' => 'Raffle Resultaten',
			'body' => '',
		],
		'pl' => [
			'slug' => 'raffle-wyniki',
			'title' => 'Raffle Wyniki',
			'body' => '',
		],
		'pt' => [
			'slug' => 'raffle-resultados',
			'title' => 'Raffle Resultados',
			'body' => '',
		],
		'ro' => [
			'slug' => 'raffle-rezultate',
			'title' => 'Raffle Rezultate',
			'body' => '',
		],
		'ru' => [
			'slug' => 'raffle-rezultaty',
			'title' => 'Raffle Rezultaty',
			'body' => '',
		],
		'sk' => [
			'slug' => 'raffle-vysledky',
			'title' => 'Raffle Vysledky',
			'body' => '',
		],
		'sl' => [
			'slug' => 'raffle-rezultati',
			'title' => 'Raffle Rezultati',
			'body' => '',
		],
		'sq' => [
			'slug' => 'raffle-rezultatet',
			'title' => 'Raffle Rezultatet',
			'body' => '',
		],
		'sr' => [
			'slug' => 'raffle-rezultati',
			'title' => 'Raffle Rezultati',
			'body' => '',
		],
		'sv' => [
			'slug' => 'raffle-resultaten',
			'title' => 'Raffle Resultaten',
			'body' => '',
		],
		'th' => [
			'slug' => 'raffle-phllaph%e1%b1h%cc%92',
			'title' => 'Raffle Phllaph%e1%b1h%cc%92',
			'body' => '',
		],
		'tr' => [
			'slug' => 'raffle-sonuclar',
			'title' => 'Raffle Sonuclar',
			'body' => '',
		],
		'uk' => [
			'slug' => 'raffle-rezultaty',
			'title' => 'Raffle Rezultaty',
			'body' => '',
		],
		'vi' => [
			'slug' => 'raffle-ket-qua',
			'title' => 'Raffle Ket-qua',
			'body' => '',
		],
		'zh' => [
			'slug' => 'raffle-jieguo',
			'title' => 'Raffle Jieguo',
			'body' => '',
		],
	]; 

}
