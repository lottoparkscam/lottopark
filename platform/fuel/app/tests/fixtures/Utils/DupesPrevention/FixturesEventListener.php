<?php

declare(strict_types=1);

namespace Tests\Fixtures\Utils\DupesPrevention;

use Orm\Model;
use Stwarog\FuelFixtures\Events\BeforePersisted;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FixturesEventListener implements EventSubscriberInterface
{
    private FeatureToggle $feature;
    private Matcher $matcher;

    public function __construct(FeatureToggle $feature, Matcher $matcher)
    {
        $this->feature = $feature;
        $this->matcher = $matcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforePersisted::class => ['handleChanges']
        ];
    }

    public function handleChanges(BeforePersisted $event): void
    {
        if ($this->feature->isDupesPreventionDisabled()) {
            return;
        }

        /** @var Model $fuelModel */
        $fuelModel = $event->getModel();
        $this->matcher->execute($fuelModel);
    }
}
