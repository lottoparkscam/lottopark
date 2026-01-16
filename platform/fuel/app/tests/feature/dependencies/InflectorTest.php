<?php

namespace Feature\Dependencies;

use Doctrine\Inflector\Inflector;
use Test_Feature;

class InflectorTest extends Test_Feature
{
    private Inflector $inflector;

    public function setUp(): void
    {
        parent::setUp();
        $this->inflector = $this->container->get(Inflector::class);
    }

    /** @test */
    public function inflect()
    {
        $this->assertSame('cities', $this->inflector->pluralize('city'));
        $this->assertSame('whitelabel_user_tickets', $this->inflector->pluralize('whitelabel_user_ticket'));
        $this->assertSame('whitelabel_cities', $this->inflector->pluralize('whitelabel_city'));
    }
}
