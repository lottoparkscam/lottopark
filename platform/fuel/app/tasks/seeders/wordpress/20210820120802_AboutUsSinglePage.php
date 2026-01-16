<?php

namespace Fuel\Tasks\Seeders\Wordpress;

use Fuel\Tasks\Seeders\Wordpress\Abstracts\AbstractPage;

/**
 * This seeder creates parent.
 */

final class AboutUsSinglePage extends AbstractPage
{
    protected const WP_DOMAIN_NAME_WITHOUT_PORT = ['lottopark'];

    protected const TITLES_AND_BODIES_PER_LANGUAGE = [
        'en' => [
            'title' => 'About us',
            'body' => "<p>We offer the possibility to play the world's biggest lotteries online.
            Our site was designed with a lottery player in mind.
            We are lotto fans ourselves, therefore we know what it takes to satisfy one.</p>
            <p>Our team is build up with lottery enthusiasts, but also industry professionals - 
            designers and developers, ensuring the smoothest lotto playing experience.
            Support is also a pillar of our operations, our agents are always thriving to help.</p>
            <p>Your satisfaction is our goal!</p>",
        ],
        'pl' => [
            'title' => 'O nas',
            'body' => 'Oferujemy możliwość gry w największe loterie świata online.
            Naszą stronę zaprojektowaliśmy z myślą o miłośnikach loterii.
            My również jesteśmy fanami lotto, więc wiemy czego gracz potrzebuje do szczęścia.
            Nasz zespół składa się zarówno z miłośników lotto, jak i branżowych profesjonalistów: 
            projektantów i deweloperów, dzięki którym gra w lotto jest tak przyjemna i wygodna jak
            nigdy wcześniej. 
            Nie można również zapomnieć o naszym dziale obsługi, który zawsze chętnie pomaga w
            wypadku jakichkolwiek pytań czy problemów.
            Twoja satysfakcja to nasz cel!',
        ]
    ];
}
