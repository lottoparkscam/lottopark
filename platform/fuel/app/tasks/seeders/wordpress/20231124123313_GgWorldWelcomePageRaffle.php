<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageRaffle;
use Models\Raffle;

final class GgWorldWelcomePageRaffle extends AbstractPageRaffle
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results-raffle' => [
				'title' => 'Results GG World Welcome Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Play GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Information GG World Welcome Raffle',
				'body' => '',
			],
		],
		'ar' => [
			'results-raffle' => [
				'title' => 'نتائج GG World Welcome Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'العب اليانصيب GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'معلومات GG World Welcome Raffle',
				'body' => '',
			],
		],
		'az' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Nəticələr',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Oyna GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Informasiya',
				'body' => '',
			],
		],
		'bg' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Резултати',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Играйте GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Информация',
				'body' => '',
			],
		],
		'bn' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle ফলাফল',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'খেলুন GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle তথ্য',
				'body' => '',
			],
		],
		'cs' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Výsledky',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Hrajte GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Informace',
				'body' => '',
			],
		],
		'da' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Resultater',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Spil GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Information',
				'body' => '',
			],
		],
		'de' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Ergebnisse',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'GG World Welcome Raffle Spielen',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Informationen',
				'body' => '',
			],
		],
		'el' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Αποτελέσματα',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Παίξτε GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Πληροφορίες',
				'body' => '',
			],
		],
		'es' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Resultados',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Jugar GG World Welcome Raffle en línea',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Información',
				'body' => '',
			],
		],
		'et' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Tulemused',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Mängige GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Informaatsion',
				'body' => '',
			],
		],
		'fil' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Results',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Maglaro ng GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Information',
				'body' => '',
			],
		],
		'fr' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Résultats',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Jouer à GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Information sur GG World Welcome Raffle',
				'body' => '',
			],
		],
		'ge' => [
			'results-raffle' => [
				'title' => 'Shedegebi GG World Welcome Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Play GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Information',
				'body' => '',
			],
		],
		'he' => [
			'results-raffle' => [
				'title' => 'תוצאות GG World Welcome Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'מידע GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle שחק',
				'body' => '',
			],
		],
		'hi' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle परिणाम',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'खेलें GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle जानकारी',
				'body' => '',
			],
		],
		'hr' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Rezultati',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Igrajte GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Informacije',
				'body' => '',
			],
		],
		'hu' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Eredmények',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Játssz GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Információ',
				'body' => '',
			],
		],
		'id' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Hasil',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Main GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Informasi',
				'body' => '',
			],
		],
		'it' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Risultati',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Gioca a GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Informazioni',
				'body' => '',
			],
		],
		'ko' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle 구매 결과',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'GG World Welcome Raffle 구매로 플레이하기',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle 구매 정보',
				'body' => '',
			],
		],
		'lt' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Rezultatai',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Žaiskite GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Informacija',
				'body' => '',
			],
		],
		'lv' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Rezultāti',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Spēlēt GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Informācija',
				'body' => '',
			],
		],
		'mk' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Резултати',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Играјте GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Информации',
				'body' => '',
			],
		],
		'nl' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle-resultaten',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Speel GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle-informatie',
				'body' => '',
			],
		],
		'pl' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Wyniki',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Zagraj w GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Informacje',
				'body' => '',
			],
		],
		'pt' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Resultados',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Jogar GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Informações de GG World Welcome Raffle',
				'body' => '',
			],
		],
		'ro' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Rezultate',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Joacă loteria GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Informație',
				'body' => '',
			],
		],
		'ru' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Результаты',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Играйте в GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Информация',
				'body' => '',
			],
		],
		'sk' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Výsledky',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Hraj GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Informácie',
				'body' => '',
			],
		],
		'sl' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Rezultati',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Igraj GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Informacije',
				'body' => '',
			],
		],
		'sq' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Rezultatet',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Luani GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Informacion',
				'body' => '',
			],
		],
		'sr' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Rezultati',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Igrati GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Informacije',
				'body' => '',
			],
		],
		'sv' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Resultat',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Spela på GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Information',
				'body' => '',
			],
		],
		'th' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle ผลลัพธ์',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'เล่น GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle ข้อมูล',
				'body' => '',
			],
		],
		'tr' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Sonuçlar',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Çevrimiçi GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Bilgi',
				'body' => '',
			],
		],
		'uk' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Результати',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Грайте в GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Інформація про GG World Welcome Raffle',
				'body' => '',
			],
		],
		'vi' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle Kết quả',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Chơi xổ số GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Welcome Raffle Thông tin',
				'body' => '',
			],
		],
		'zh' => [
			'results-raffle' => [
				'title' => 'GG World Welcome Raffle 彩票结果和开奖数字',
				'body' => '',
			],
			'play-raffle' => [
				'title' => '在线玩 GG World Welcome Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => '在线 GG World Welcome Raffle',
				'body' => '',
			],
		],
	];
	protected const IS_PARENT = false;
	protected const GAME_NAME_SLUG = Raffle::GG_WORLD_WELCOME_RAFFLE_SLUG;
}
