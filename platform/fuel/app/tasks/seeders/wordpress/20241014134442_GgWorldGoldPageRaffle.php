<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageRaffle;
use Models\Raffle;

final class GgWorldGoldPageRaffle extends AbstractPageRaffle
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results-raffle' => [
				'title' => 'Results GG World Gold Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Play GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Information GG World Gold Raffle',
				'body' => '',
			],
		],
		'ar' => [
			'results-raffle' => [
				'title' => 'نتائج GG World Gold Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'العب اليانصيب GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'معلومات GG World Gold Raffle',
				'body' => '',
			],
		],
		'az' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Nəticələr',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Oyna GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Informasiya',
				'body' => '',
			],
		],
		'bg' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Резултати',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Играйте GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Информация',
				'body' => '',
			],
		],
		'bn' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle ফলাফল',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'খেলুন GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle তথ্য',
				'body' => '',
			],
		],
		'cs' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Výsledky',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Hrajte GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Informace',
				'body' => '',
			],
		],
		'da' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Resultater',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Spil GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Information',
				'body' => '',
			],
		],
		'de' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Ergebnisse',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'GG World Gold Raffle Spielen',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Informationen',
				'body' => '',
			],
		],
		'el' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Αποτελέσματα',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Παίξτε GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Πληροφορίες',
				'body' => '',
			],
		],
		'es' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Resultados',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Jugar GG World Gold Raffle en línea',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Información',
				'body' => '',
			],
		],
		'et' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Tulemused',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Mängige GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Informaatsion',
				'body' => '',
			],
		],
		'fa' => [
			'results-raffle' => [
				'title' => 'نتایج GG World Gold Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'بازی GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'اطلاعات GG World Gold Raffle',
				'body' => '',
			],
		],
		'fi' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Tulokset',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Pelaa GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Tiedot',
				'body' => '',
			],
		],
		'fil' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Results',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Maglaro ng GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Information',
				'body' => '',
			],
		],
		'fr' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Résultats',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Jouer à GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Information sur GG World Gold Raffle',
				'body' => '',
			],
		],
		'ge' => [
			'results-raffle' => [
				'title' => 'Shedegebi GG World Gold Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Play GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Information',
				'body' => '',
			],
		],
		'he' => [
			'results-raffle' => [
				'title' => 'תוצאות GG World Gold Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'מידע GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle שחק',
				'body' => '',
			],
		],
		'hi' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle परिणाम',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'खेलें GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle जानकारी',
				'body' => '',
			],
		],
		'hr' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Rezultati',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Igrajte GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Informacije',
				'body' => '',
			],
		],
		'hu' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Eredmények',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Játssz GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Információ',
				'body' => '',
			],
		],
		'id' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Hasil',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Main GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Informasi',
				'body' => '',
			],
		],
		'it' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Risultati',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Gioca a GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Informazioni',
				'body' => '',
			],
		],
		'ja' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle 結果',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'GG World Gold Raffle をプレイ',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle 情報',
				'body' => '',
			],
		],
		'ko' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle 구매 결과',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'GG World Gold Raffle 구매로 플레이하기',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle 구매 정보',
				'body' => '',
			],
		],
		'lt' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Rezultatai',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Žaiskite GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Informacija',
				'body' => '',
			],
		],
		'lv' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Rezultāti',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Spēlēt GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Informācija',
				'body' => '',
			],
		],
		'mk' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Резултати',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Играјте GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Информации',
				'body' => '',
			],
		],
		'nl' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle-resultaten',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Speel GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle-informatie',
				'body' => '',
			],
		],
		'pl' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Wyniki',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Zagraj w GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Informacje',
				'body' => '',
			],
		],
		'pt' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Resultados',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Jogar GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Informações de GG World Gold Raffle',
				'body' => '',
			],
		],
		'ro' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Rezultate',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Joacă loteria GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Informație',
				'body' => '',
			],
		],
		'ru' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Результаты',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Играйте в GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Информация',
				'body' => '',
			],
		],
		'sk' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Výsledky',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Hraj GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Informácie',
				'body' => '',
			],
		],
		'sl' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Rezultati',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Igraj GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Informacije',
				'body' => '',
			],
		],
		'sq' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Rezultatet',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Luani GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Informacion',
				'body' => '',
			],
		],
		'sr' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Rezultati',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Igrati GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Informacije',
				'body' => '',
			],
		],
		'sv' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Resultat',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Spela på GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Information',
				'body' => '',
			],
		],
		'th' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle ผลลัพธ์',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'เล่น GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle ข้อมูล',
				'body' => '',
			],
		],
		'tr' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Sonuçlar',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Çevrimiçi GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Bilgi',
				'body' => '',
			],
		],
		'uk' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Результати',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Грайте в GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Інформація про GG World Gold Raffle',
				'body' => '',
			],
		],
		'vi' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle Kết quả',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Chơi xổ số GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Gold Raffle Thông tin',
				'body' => '',
			],
		],
		'zh' => [
			'results-raffle' => [
				'title' => 'GG World Gold Raffle 彩票结果和开奖数字',
				'body' => '',
			],
			'play-raffle' => [
				'title' => '在线玩 GG World Gold Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => '在线 GG World Gold Raffle',
				'body' => '',
			],
		],
	];
	protected const IS_PARENT = false;
	protected const GAME_NAME_SLUG = Raffle::GG_WORLD_GOLD_RAFFLE_SLUG;
}
