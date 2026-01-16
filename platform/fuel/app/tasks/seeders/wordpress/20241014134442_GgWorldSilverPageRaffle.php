<?php

namespace Fuel\Tasks\Seeders\Wordpress;
use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPageRaffle;
use Models\Raffle;

final class GgWorldSilverPageRaffle extends AbstractPageRaffle
{
	protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
	protected const TITLES_AND_BODIES_PER_LANGUAGE = [
		'en' => [
			'results-raffle' => [
				'title' => 'Results GG World Silver Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Play GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Information GG World Silver Raffle',
				'body' => '',
			],
		],
		'ar' => [
			'results-raffle' => [
				'title' => 'نتائج GG World Silver Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'العب اليانصيب GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'معلومات GG World Silver Raffle',
				'body' => '',
			],
		],
		'az' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Nəticələr',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Oyna GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Informasiya',
				'body' => '',
			],
		],
		'bg' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Резултати',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Играйте GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Информация',
				'body' => '',
			],
		],
		'bn' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle ফলাফল',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'খেলুন GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle তথ্য',
				'body' => '',
			],
		],
		'cs' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Výsledky',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Hrajte GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Informace',
				'body' => '',
			],
		],
		'da' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Resultater',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Spil GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Information',
				'body' => '',
			],
		],
		'de' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Ergebnisse',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'GG World Silver Raffle Spielen',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Informationen',
				'body' => '',
			],
		],
		'el' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Αποτελέσματα',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Παίξτε GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Πληροφορίες',
				'body' => '',
			],
		],
		'es' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Resultados',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Jugar GG World Silver Raffle en línea',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Información',
				'body' => '',
			],
		],
		'et' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Tulemused',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Mängige GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Informaatsion',
				'body' => '',
			],
		],
		'fa' => [
			'results-raffle' => [
				'title' => 'نتایج GG World Silver Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'بازی GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'اطلاعات GG World Silver Raffle',
				'body' => '',
			],
		],
		'fi' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Tulokset',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Pelaa GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Tiedot',
				'body' => '',
			],
		],
		'fil' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Results',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Maglaro ng GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Information',
				'body' => '',
			],
		],
		'fr' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Résultats',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Jouer à GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Information sur GG World Silver Raffle',
				'body' => '',
			],
		],
		'ge' => [
			'results-raffle' => [
				'title' => 'Shedegebi GG World Silver Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Play GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Information',
				'body' => '',
			],
		],
		'he' => [
			'results-raffle' => [
				'title' => 'תוצאות GG World Silver Raffle',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'מידע GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle שחק',
				'body' => '',
			],
		],
		'hi' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle परिणाम',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'खेलें GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle जानकारी',
				'body' => '',
			],
		],
		'hr' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Rezultati',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Igrajte GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Informacije',
				'body' => '',
			],
		],
		'hu' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Eredmények',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Játssz GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Információ',
				'body' => '',
			],
		],
		'id' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Hasil',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Main GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Informasi',
				'body' => '',
			],
		],
		'it' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Risultati',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Gioca a GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Informazioni',
				'body' => '',
			],
		],
		'ja' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle 結果',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'GG World Silver Raffle をプレイ',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle 情報',
				'body' => '',
			],
		],
		'ko' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle 구매 결과',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'GG World Silver Raffle 구매로 플레이하기',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle 구매 정보',
				'body' => '',
			],
		],
		'lt' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Rezultatai',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Žaiskite GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Informacija',
				'body' => '',
			],
		],
		'lv' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Rezultāti',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Spēlēt GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Informācija',
				'body' => '',
			],
		],
		'mk' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Резултати',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Играјте GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Информации',
				'body' => '',
			],
		],
		'nl' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle-resultaten',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Speel GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle-informatie',
				'body' => '',
			],
		],
		'pl' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Wyniki',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Zagraj w GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Informacje',
				'body' => '',
			],
		],
		'pt' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Resultados',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Jogar GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Informações de GG World Silver Raffle',
				'body' => '',
			],
		],
		'ro' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Rezultate',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Joacă loteria GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Informație',
				'body' => '',
			],
		],
		'ru' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Результаты',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Играйте в GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Информация',
				'body' => '',
			],
		],
		'sk' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Výsledky',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Hraj GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Informácie',
				'body' => '',
			],
		],
		'sl' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Rezultati',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Igraj GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Informacije',
				'body' => '',
			],
		],
		'sq' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Rezultatet',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Luani GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Informacion',
				'body' => '',
			],
		],
		'sr' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Rezultati',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Igrati GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Informacije',
				'body' => '',
			],
		],
		'sv' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Resultat',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Spela på GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Information',
				'body' => '',
			],
		],
		'th' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle ผลลัพธ์',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'เล่น GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle ข้อมูล',
				'body' => '',
			],
		],
		'tr' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Sonuçlar',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Çevrimiçi GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Bilgi',
				'body' => '',
			],
		],
		'uk' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Результати',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Грайте в GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'Інформація про GG World Silver Raffle',
				'body' => '',
			],
		],
		'vi' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle Kết quả',
				'body' => '',
			],
			'play-raffle' => [
				'title' => 'Chơi xổ số GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => 'GG World Silver Raffle Thông tin',
				'body' => '',
			],
		],
		'zh' => [
			'results-raffle' => [
				'title' => 'GG World Silver Raffle 彩票结果和开奖数字',
				'body' => '',
			],
			'play-raffle' => [
				'title' => '在线玩 GG World Silver Raffle',
				'body' => '',
			],
			'information-raffle' => [
				'title' => '在线 GG World Silver Raffle',
				'body' => '',
			],
		],
	];
	protected const IS_PARENT = false;
	protected const GAME_NAME_SLUG = Raffle::GG_WORLD_SILVER_RAFFLE_SLUG;
}
