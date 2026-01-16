<?php

namespace Modules\Mediacle\Models;

interface TrackingIdAwareContract
{
    /**
     * Id of tracking system.
     * @return string
     */
    public function getTrackingId(): ?string;

    public function getBtag(): ?string;
}
