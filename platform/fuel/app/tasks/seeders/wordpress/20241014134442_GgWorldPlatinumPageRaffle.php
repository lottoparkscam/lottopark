<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageRaffle;
use Models\Raffle;

final class GgWorldPlatinumPageRaffle extends AbstractPageRaffle
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results-raffle' => [
				'title' => 'Results GG World Platinum Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Play GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Information GG World Platinum Raffle',
				'body' => '',
			],
		],
		'ar' => [
			'results-raffle' => [
				'title' => 'نتائج GG World Platinum Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'العب اليانصيب GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'معلومات GG World Platinum Raffle',
				'body' => '',
			],
		],
		'az' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Nəticələr',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Oyna GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Informasiya',
				'body' => '',
			],
		],
		'bg' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Резултати',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Играйте GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Информация',
				'body' => '',
			],
		],
		'bn' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle ফলাফল',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'খেলুন GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle তথ্য',
				'body' => '',
			],
		],
		'cs' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Výsledky',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Hrajte GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Informace',
				'body' => '',
			],
		],
		'da' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Resultater',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Spil GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Information',
				'body' => '',
			],
		],
		'de' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Ergebnisse',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'GG World Platinum Raffle Spielen',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Informationen',
				'body' => '',
			],
		],
		'el' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Αποτελέσματα',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Παίξτε GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Πληροφορίες',
				'body' => '',
			],
		],
		'es' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Resultados',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Jugar GG World Platinum Raffle en línea',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Información',
				'body' => '',
			],
		],
		'et' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Tulemused',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Mängige GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Informaatsion',
				'body' => '',
			],
		],
		'fa' => [
			'results-raffle' => [
				'title' => 'نتایج GG World Platinum Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'بازی GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'اطلاعات GG World Platinum Raffle',
				'body' => '',
			],
		],
		'fi' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Tulokset',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Pelaa GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Tiedot',
				'body' => '',
			],
		],
		'fil' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Results',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Maglaro ng GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Information',
				'body' => '',
			],
		],
		'fr' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Résultats',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Jouer à GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Information sur GG World Platinum Raffle',
				'body' => '',
			],
		],
		'ge' => [
			'results-raffle' => [
				'title' => 'Shedegebi GG World Platinum Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Play GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Information',
				'body' => '',
			],
		],
		'he' => [
			'results-raffle' => [
				'title' => 'תוצאות GG World Platinum Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'מידע GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle שחק',
				'body' => '',
			],
		],
		'hi' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle परिणाम',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'खेलें GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle जानकारी',
				'body' => '',
			],
		],
		'hr' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Rezultati',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Igrajte GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Informacije',
				'body' => '',
			],
		],
		'hu' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Eredmények',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Játssz GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Információ',
				'body' => '',
			],
		],
		'id' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Hasil',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Main GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Informasi',
				'body' => '',
			],
		],
		'it' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Risultati',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Gioca a GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Informazioni',
				'body' => '',
			],
		],
		'ja' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle 結果',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'GG World Platinum Raffle をプレイ',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle 情報',
				'body' => '',
			],
		],
		'ko' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle 구매 결과',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'GG World Platinum Raffle 구매로 플레이하기',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle 구매 정보',
				'body' => '',
			],
		],
		'lt' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Rezultatai',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Žaiskite GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Informacija',
				'body' => '',
			],
		],
		'lv' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Rezultāti',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Spēlēt GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Informācija',
				'body' => '',
			],
		],
		'mk' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Резултати',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Играјте GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Информации',
				'body' => '',
			],
		],
		'nl' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle-resultaten',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Speel GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle-informatie',
				'body' => '',
			],
		],
		'pl' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Wyniki',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Zagraj w GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Informacje',
				'body' => '',
			],
		],
		'pt' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Resultados',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Jogar GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Informações de GG World Platinum Raffle',
				'body' => '',
			],
		],
		'ro' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Rezultate',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Joacă loteria GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Informație',
				'body' => '',
			],
		],
		'ru' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Результаты',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Играйте в GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Информация',
				'body' => '',
			],
		],
		'sk' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Výsledky',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Hraj GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Informácie',
				'body' => '',
			],
		],
		'sl' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Rezultati',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Igraj GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Informacije',
				'body' => '',
			],
		],
		'sq' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Rezultatet',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Luani GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Informacion',
				'body' => '',
			],
		],
		'sr' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Rezultati',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Igrati GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Informacije',
				'body' => '',
			],
		],
		'sv' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Resultat',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Spela på GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Information',
				'body' => '',
			],
		],
		'th' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle ผลลัพธ์',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'เล่น GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle ข้อมูล',
				'body' => '',
			],
		],
		'tr' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Sonuçlar',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Çevrimiçi GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Bilgi',
				'body' => '',
			],
		],
		'uk' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Результати',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Грайте в GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Інформація про GG World Platinum Raffle',
				'body' => '',
			],
		],
		'vi' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle Kết quả',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Chơi xổ số GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Platinum Raffle Thông tin',
				'body' => '',
			],
		],
		'zh' => [
			'results-raffle' => [
				'title' => 'GG World Platinum Raffle 彩票结果和开奖数字',
				'body' => '',
			],
			'play-raffle' => [
				'title' => '在线玩 GG World Platinum Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => '在线 GG World Platinum Raffle',
				'body' => '',
			],
		],
	];
	protected const IS_PARENT = false;
	protected const GAME_NAME_SLUG = Raffle::GG_WORLD_PLATINUM_RAFFLE_SLUG;
}
