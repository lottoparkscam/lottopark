<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractAddTranslationToSinglePage;

final class AboutUsSinglePageAddTranslation extends AbstractAddTranslationToSinglePage
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];
    protected const PAGE_TYPE = 'page';
    protected const PARENT_SLUG = 'about-us';
    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'es' => [
            'slug' => 'acerca-de-nosotros',
            'title' => 'Acerca de nosotros',
            'body' => '<p>El sitio web [whitelabelDomain] es operado por [whitelabelCompany]</p>
            <p>[whitelabelName] se ha fijado la misión de dar a la gente la oportunidad de jugar las loterías
            más grandes del mundo: SuperEnalotto, Powerball, UK Lotto, EuroMillions, Mega Millions,
            Eurojackpot.</p>
            <p>Somos un equipo de profesionales de la industria, con más de 50 años de experiencia, 
            y aspiramos a ser los mejores en nuestro campo, centrándonos en brindar la mejor experiencia a
             nuestra clientela.</p>
            <p>No somos los organizadores de las loterías ofrecidas. Simplemente somos tus representantes,
            compramos por ti el billete en la agencia de lotería local de los países correspondientes, 
            donde la lotería en particular está disponible.</p>
            <p>Nos preocupamos por tu comodidad, la seguridad y la posibilidad de comprar billetes, 
            que te permitan participar en la lotería más grande.</p>',
        ],
        'de' => [
            'slug' => 'ueber-uns',
            'title' => 'Über Uns',
            'body' => '<p>[whitelabelName] hat sich selbst das Ziel gesetzt, Menschen auf der ganzen Welt die
            Möglichkeit zu geben, die größten Lotterien der Welt zu spielen: SuperEnalotto, Powerball, 
            UK Lotto, EuroMillions, Mega Millions, Eurojackpot.</p>
            <p>Wir sind ein Team von exzellenten Industrieexperten, mit insgesamt mehr als 50 Bahrein
            Erfahrung, und arbeiten daran, die Besten in unserem Bereich zu werden, in dem wir uns auf 
            die beste Kundenerfahrung konzentrieren</p>
            <p>Wir sind nicht die Organisatoren der angebotenen Lotterien. Wir sind Ihre Repräsentanten,
            welche die Scheine an den lokalen Lotterieläden in Ländern kaufen, wo die jeweilige 
            Lotterie verfügbar ist.</p>
            <p>Wir kümmern uns um Annehmlichkeiten, Sicherheit und die Möglichkeit, Lotteriescheine 
            zu kaufen, was Ihnen die Teilnahem an den größten Lotterieauslosungen ermöglicht.</p>
             ',
        ],
    ];
}
