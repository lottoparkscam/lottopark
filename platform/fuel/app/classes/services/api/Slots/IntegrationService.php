<?php


namespace Services\Api\Slots;

use Exception;
use Fuel\Core\Cli;
use Models\Whitelabel;
use Models\WhitelabelSlotProvider;
use Repositories\SlotProviderRepository;
use Repositories\WhitelabelRepository;
use Repositories\WhitelabelSlotProviderRepository;

class IntegrationService
{
    private WhitelabelRepository $whitelabelRepository;
    private SlotProviderRepository $slotProviderRepository;
    private WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository;
    protected Whitelabel $whitelabel;

    public function __construct(
        WhitelabelRepository $whitelabelRepository,
        SlotProviderRepository $slotProviderRepository,
        WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository
    ) {
        $this->whitelabelRepository = $whitelabelRepository;
        $this->slotProviderRepository = $slotProviderRepository;
        $this->whitelabelSlotProviderRepository = $whitelabelSlotProviderRepository;
    }

    /** @throws Exception */
    public function addWhitelabelSlotProvider(string $whitelabelTheme, string $slotProviderSlug): void
    {
        $whitelabel = $this->whitelabelRepository->findOneByTheme($whitelabelTheme);
        $this->whitelabel = $whitelabel;

        $slotProvider = $this->slotProviderRepository->findOneBySlug($slotProviderSlug);
        $whitelabelSlotProvider = $this->whitelabelSlotProviderRepository->findByWhitelabelAndProviderSlug(
            $whitelabel->id,
            $slotProviderSlug
        );

        if (!empty($whitelabelSlotProvider)) {
            throw new Exception('WhitelabelSlotProvider already exists!');
        }

        $whitelabelSlotProvider = new WhitelabelSlotProvider([
            'whitelabel_id' => $whitelabel->id,
            'slot_provider_id' => $slotProvider->id
        ]);
        $whitelabelSlotProvider->save();
    }

    public function runWordpressSeeders(): void
    {
        $whitelabelDomain = str_replace(['.loc', '.work'], '', $this->whitelabel->domain);

        $seedersNames = [
            'CasinoPage',
            'CasinoPlayPage',
            'CasinoFooterPage',
            'CasinoPrivacyPage',
            'CasinoTermsPage',
            'CasinoFooterMenu',
            'CasinoPrimaryMenu',
            'HomeToCasinoFooterNavButton',
            'CasinoToFooterNavButton',
            'PrivacyPolicyToCasinoFooterNavButton',
            'TermsAndConditionsToCasinoFooterNavButton',
            'LotteriesToCasinoFooterNavButton',
            'LotteriesToHeaderNavButton',
            'CasinoToHeaderNavButton',
            'CasinoLobbyPage',
            'ContactToCasinoPrimaryMenuNavButton'
        ];

        $isLottoParkOrLocal = $this->whitelabel->id === 1;
        if ($isLottoParkOrLocal) {
            $seedersNames[] = 'CasinoPromotionPage';
        }

        $seededSeeders = [];
        $notSeededSeeders = [];

        foreach ($seedersNames as $name) {
            $isError = false;

            $wordpressSeederOutput = [];
            exec("php8.0 oil r seed_wordpress $whitelabelDomain $name", $wordpressSeederOutput);

            foreach ($wordpressSeederOutput as $outputLine) {
                echo $outputLine . "\n";

                if (!$isError) {
                    $isError = str_contains($outputLine, 'error');
                }

                if ($isError) {
                    $notSeededSeeders[] = $name;
                }
            }
        }

        $seededSeeders = array_diff($notSeededSeeders, $seedersNames);
        if (empty($seededSeeders)) {
            $seededSeeders = $seedersNames;
        }

        Cli::write("Successfully seeded (or was seeded before): \n");
        Cli::write($seededSeeders);

        Cli::write("Failed to seed: \n");
        Cli::write($notSeededSeeders);
    }
}