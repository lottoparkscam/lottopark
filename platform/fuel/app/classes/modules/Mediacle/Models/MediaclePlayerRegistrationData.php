<?php

namespace Modules\Mediacle\Models;

interface MediaclePlayerRegistrationData extends MediaclePlayerData
{
    public function getFirstName(): ?string;

    public function getLastName(): ?string;

    public function getEmail(): string;

    public function getPhoneNumber(): ?string;

    public function getCompany(): ?string;

    /**
     * This is WhitelabelPlugin -> options key value irl.
     * @return string
     */
    public function getTrackingIdentityKey(): string;
}
