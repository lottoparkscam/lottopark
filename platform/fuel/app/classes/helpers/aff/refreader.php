<?php

use Repositories\Aff\WhitelabelUserAffRepository;
use Services\AffService;

/**
 * This class is responsible for reading values from affiliates ref.
 *
 * @author Marcin
 */
class Helpers_Aff_Refreader
{
    private $token = null;

    /**
     * Name of the cookie or session parameter, from which ref will be read.
     */
    const REF_NAME = Helpers_General::COOKIE_AFF_NAME;

    private AffService $affService;
    private WhitelabelUserAffRepository $whitelabelUserAffRepository;

    public function __construct(AffService $affService, WhitelabelUserAffRepository $whitelabelUserAffRepository)
    {
        $this->affService = $affService;
        $this->whitelabelUserAffRepository = $whitelabelUserAffRepository;
    }

    /**
     * Check if ref token is valid.
     * @return bool true if ref is valid, false otherwise.
     */
    public function isRefValid(): bool
    {
        $this->token = $this->affService->getPropertyFromCookie(self::REF_NAME);
        return $this->token !== null;
    }

    /**
     * Get ref token.
     * Note that token is refreshed on every call to is_ref_valid!
     * @return string ref token or null if it doesn't exits.
     */
    public function get_ref_token()
    {
        // return token based on settings
        return $this->token;
    }

    /** Validate being a lead for user, if lead is expired according to settings it will expire it. */
    public function validateLead(int $whitelabelId, ?string $refToken, array $user): void
    {
        if (empty($refToken)) {
            return;
        }

        $aff = $this->whitelabelUserAffRepository->findUserAffiliateByWhitelabelIdAndRefToken($whitelabelId, $refToken, $user['id']);

        // check if aff is ok
        if ($aff === null) {
            return; // break, no use in proceeding without aff
        }

        $aff = $aff->to_array();

        // validate lead
        $leadValidator = new Helpers_Aff_Leadvalidator();
        if ($leadValidator->is_lead_outdated($user, $aff)) {
            Model_Whitelabel_User_Aff::expire_lead($user['id']); // user is lead, but it's outdated - expire it
        }
    }
}
