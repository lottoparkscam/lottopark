<?php

namespace Fuel\Tasks;

use Fuel\Core\File;
use Fuel\Core\Cli;
use Helpers_Time;
use Helper_File;
use Task_Cli;

final class ForgeWordpressSeeder extends Task_Cli
{
    private const SEEDERS_TYPES = [
        'lottery' => [
            'bodyType' => 'lottery',
            'abstract' => 'AbstractPageLottery',
            'description' => 'Add new lottery game.',
        ],
        'lotteryParent' => [
            'bodyType' => 'lottery',
            'abstract' => 'AbstractPageLottery',
            'description' => 'Add new parent lottery.',
        ],
        'raffle' => [
            'bodyType' => 'raffle',
            'abstract' => 'AbstractPageRaffle',
            'description' => 'Add new raffle game.'
        ],
        'raffleParent' => [
            'bodyType' => 'raffle',
            'abstract' => 'AbstractPageRaffle',
            'description' => 'Add new raffle parent.'
        ],
        'addTranslationToRaffle' => [
            'bodyType' => 'raffle',
            'abstract' => 'AbstractAddTranslationToGame',
            'description' => 'Add translation to existing raffle page.'
        ],
        'addTranslationToRaffleParent' => [
            'bodyType' => 'raffle',
            'abstract' => 'AbstractAddTranslationToGame',
            'description' => 'Add translation to existing raffle parent page.'
        ],
        'addTranslationToLottery' => [
            'bodyType' => 'lottery',
            'abstract' => 'AbstractAddTranslationToGame',
            'description' => 'Add translation to existing lottery page.'
        ],
        'addTranslationToLotteryParent' => [
            'bodyType' => 'lottery',
            'abstract' => 'AbstractAddTranslationToGame',
            'description' => 'Add translation to existing lottery parent page.'
        ],
        'page' => [
            'bodyType' => 'page',
            'abstract' => 'AbstractPage',
            'description' => 'Add new page.'
        ],
        'addTranslationToPage' => [
            'bodyType' => 'page',
            'abstract' => 'AbstractAddTranslationToSinglePage',
            'description' => 'Add translation to existing page.'
        ],
        'post' => [
            'bodyType' => 'post',
            'abstract' => 'AbstractPost',
            'description' => 'Add new post.'
        ],
        'addTranslationToPost' => [
            'bodyType' => 'post',
            'abstract' => 'AbstractAddTranslationToSinglePage',
            'description' => 'Add translation to existing post.'
        ],
        'navButton' => [
            'bodyType' => 'navButton',
            'abstract' => 'AbstractNavigation',
            'description' => 'Add new nav button (link) to provided menu.'
        ],
        'addTranslationToNavButton' => [
            'bodyType' => 'navButton',
            'abstract' => 'AbstractAddTranslationToNavigation',
            'description' => 'Add translation to existing nav button.'
        ],
    ];

    private const CONSTANTS = [
        'lotteryParent' => [
            'WP_DOMAIN_NAME_WITHOUT_PORT' => [],
            'TITLES_AND_BODIES_PER_LANGUAGE' => [],
            'IS_PARENT' => true
        ],
        'lottery' => [
            'WP_DOMAIN_NAME_WITHOUT_PORT' => [],
            'TITLES_AND_BODIES_PER_LANGUAGE' => [],
            'CATEGORY_NAME' => '',
            'GAME_NAME_SLUG' => '',
            'IS_PARENT' => false
        ],
        'addTranslationToLottery' => [
            'WP_DOMAIN_NAME_WITHOUT_PORT' => [],
            'TITLES_AND_BODIES_PER_LANGUAGE' => [],
            'GAME_TYPE' => 'lottery',
            'GAME_NAME_SLUG' => '',
            'PARENT_SLUG' => '',
        ],
        'addTranslationToLotteryParent' => [
            'WP_DOMAIN_NAME_WITHOUT_PORT' => [],
            'TITLES_AND_BODIES_PER_LANGUAGE' => [],
            'GAME_TYPE' => 'lottery',
            'GAME_NAME_SLUG' => '',
            'PARENT_SLUG' => '',
            'IS_PARENT' => true,
        ],
        'raffleParent' => [
            'WP_DOMAIN_NAME_WITHOUT_PORT' => [],
            'TITLES_AND_BODIES_PER_LANGUAGE' => [],
            'IS_PARENT' => true
        ],
        'raffle' => [
            'WP_DOMAIN_NAME_WITHOUT_PORT' => [],
            'TITLES_AND_BODIES_PER_LANGUAGE' => [],
            'IS_PARENT' => false,
            'GAME_NAME_SLUG' => '',
        ],
        'addTranslationToRaffleParent' => [
            'WP_DOMAIN_NAME_WITHOUT_PORT' => [],
            'TITLES_AND_BODIES_PER_LANGUAGE' => [],
            'GAME_TYPE' => 'raffle',
            'GAME_NAME_SLUG' => '',
            'PARENT_SLUG' => '',
            'IS_PARENT' => true,
        ],
        'addTranslationToRaffle' => [
            'WP_DOMAIN_NAME_WITHOUT_PORT' => [],
            'TITLES_AND_BODIES_PER_LANGUAGE' => [],
            'IS_PARENT' => false,
            'GAME_TYPE' => 'raffle',
            'GAME_NAME_SLUG' => '',
            'PARENT_SLUG' => '',
        ],
        'page' => [
            'WP_DOMAIN_NAME_WITHOUT_PORT' => [],
            'TITLES_AND_BODIES_PER_LANGUAGE' => [],
        ],
        'addTranslationToPage' => [
            'WP_DOMAIN_NAME_WITHOUT_PORT' => [],
            'TITLES_AND_BODIES_PER_LANGUAGE' => [],
            'PAGE_TYPE' => 'page',
            'PARENT_SLUG' => ''
        ],
        'addTranslationToPost' => [
            'WP_DOMAIN_NAME_WITHOUT_PORT' => [],
            'TITLES_AND_BODIES_PER_LANGUAGE' => [],
            'PAGE_TYPE' => 'post',
            'PARENT_SLUG' => '',
            'CATEGORY_NAME' => '',
            'CATEGORY_SLUG' => '',
        ],
        'post' => [
            'POST_CATEGORY_SLUG',
            'WP_DOMAIN_NAME_WITHOUT_PORT' => [],
            'TITLES_AND_BODIES_PER_LANGUAGE' => [],
        ],
        'navButton' => [
            'WP_DOMAIN_NAME_WITHOUT_PORT' => [],
            'TITLES_AND_BODIES_PER_LANGUAGE' => [],
            'SLUG_FOR_LINK' => '',
            'MENU' => '',
        ],
        'addTranslationToNavButton' => [
            'WP_DOMAIN_NAME_WITHOUT_PORT' => [],
            'TITLES_AND_BODIES_PER_LANGUAGE' => [],
            'SLUG_FOR_LINK' => '',
            'ENGLISH_TAB_TITLE' => '',
            'MENU' => '',
        ],
    ];

    /**
     * Those values will be required during php oil r forge:wpSeeder
     * Based on provided type it chooses needed values.
     */
    private const CONSTANTS_QUESTIONS = [
        'WP_DOMAIN_NAME_WITHOUT_PORT' => 'Which domains do you want to seed? (for multiple, pass it with commas)',
        'CATEGORY_NAME' => 'Category name: ',
        'CATEGORY_SLUG' => 'Category slug: ',
        'GAME_NAME_SLUG' => 'Game slug: ',
        'PARENT_SLUG' => 'Add translation to site with slug: ',
        'POST_CATEGORY_SLUG' => 'Category slug: ',
        'SLUG_FOR_LINK' => 'This nav button should have link to site with english slug:',
        'ENGLISH_TAB_TITLE' => 'Which name has the english tab?: (example: about us)',
        'MENU' => 'Choose menu where a new nav tab should be added:'
    ];

    private const TITLES_AND_BODIES_EXAMPLES = [
        'lottery' => [
            'language' => [
                'results' => [
                    'title' => '',
                    'body' => '',
                ],
                'play' => [
                    'title' => '',
                    'body' => '',
                ],
                'lotteries' => [
                    'title' => '',
                    'body' => '',
                ]
            ]
        ],
        'raffle' => [
            'language' => [
                'results-raffle' => [
                    'title' => '',
                    'body' => '',
                ],
                'play-raffle' => [
                    'title' => '',
                    'body' => '',
                ],
                'information-raffle' => [
                    'title' => '',
                    'body' => '',
                ],
                'purchase' => [
                    'body' => '',
                ],
            ],
        ],
        'page' => [
            'language' => [
                'slug' => '',
                'title' => '',
                'body' => '',
            ],
        ],
        'post' => [
            'language' => [
                'slug' => '',
                'title' => '',
                'body' => '',
            ],
        ],
        'navButton' => [
            'language' => ''
        ],
    ];

    /** casino-* menus will be available after 380-slots branch release */
    private const AVAILABLE_MENUS = [
        'primary',
        'footer',
        'casino-primary',
        'casino-footer'
    ];

    private string $classNameWithType;
    private string $seederType;
    private string $fileName;
    private string $languages;
    private string $wordpressSeedersLocation;
    private string $seedSameForMissingLanguages;

    public function __construct()
    {
        $this->disableOnProduction();
    }

    public function before(string $className): void
    {
        $this->wordpressSeedersLocation = Helper_File::app_path('tasks/seeders/wordpress');

        $this->seederType = Cli::prompt("Which type do you want to seed? \n", array_keys(self::SEEDERS_TYPES));
        $this->classNameWithType = $className . ucfirst($this->seederType);
        $this->fileName = Helpers_Time::migration_time_prefix() . '_' . $this->classNameWithType . '.php';
    }

    private function displayAvailableTypes(): void
    {
        foreach (self::SEEDERS_TYPES as $type => $typeDetails) {
            echo "{$type} - {$typeDetails['description']} \n";
        }

        echo "\n\n\t\t\t [1] means  that the field is required \n\n";
    }

    private function stopWhenFileExists(): void
    {
        $filesWithSimilarName = glob($this->wordpressSeedersLocation . "/*" . $this->classNameWithType . "*");
        $fileExists = count($filesWithSimilarName) > 0;

        if ($fileExists) {
            $this->error("Seeder with provided name ({$this->classNameWithType}) already exists!");
        }
    }

    private function error(string $error): void
    {
        Cli::write("\n $error \n");
        die;
    }

    private function askForLanguages(): void
    {
        $isRequired = [true];
        $this->languages = Cli::prompt("Which languages do you want to seed? (comma separated) \n", $isRequired);
    }

    private function askDoSeedSameForMissingLanguages(): void
    {
        $isRequired = [true];
        $this->seedSameForMissingLanguages = Cli::prompt("As default seeder seeds all missing languages with english titles and bodies. Do You want to turn this function off? (y/n)", $isRequired);
    }

    public function createFile(): void
    {
        $this->displayAvailableTypes();
        $this->stopWhenFileExists();
        $this->askDoSeedSameForMissingLanguages();
        $this->askForLanguages();

        $commonContent = $this->generateCommonContent($this->classNameWithType, $this->seederType);
        $languagesArray = explode(',', $this->languages);
        $contentWithConstants = $this->addConstantsToContent($this->seederType, $commonContent, $languagesArray);

        $seederContent = $contentWithConstants;
        File::create($this->wordpressSeedersLocation, $this->fileName, $seederContent);
    }

    private function generateCommonContent(string $className, string $seederType): string
    {
        $abstractClassName = self::SEEDERS_TYPES[$seederType]['abstract'];
        return <<<CommonContent
        <?php

        namespace Fuel\Tasks\Seeders\Wordpress;
        use Fuel\Tasks\Seeders\Wordpress\Abstracts\\$abstractClassName;

        final class $className extends $abstractClassName
        {
        CONSTANTS HERE
        }
        CommonContent;
    }

    private function addConstantsToContent(string $seederType, string $commonContent, array $languages): string
    {
        $constants = self::CONSTANTS[$seederType];
        if ($this->seedSameForMissingLanguages === "y") {
            $constants['SEED_THE_SAME_AS_ENGLISH_FOR_MISSING_LANGUAGES'] = false;
        }

        $isNavType = $seederType === 'navButton';
        if ($isNavType && Cli::prompt('Is wordpress page? (y/n)', [true]) === 'n') {
            $constants['IS_NOT_WORDPRESS_PAGE'] = true;
            $constants['DIRECT_LINK'] = Cli::prompt('Link to page:', [true]);
            unset($constants['SLUG_FOR_LINK']);
        }

        $constantsString = '';

        foreach ($constants as $constant => $value) {
            $isFieldFillable = !empty(self::CONSTANTS_QUESTIONS[$constant]);
            $isRequired = [true];
            $isMenu = $constant === 'MENU';

            if ($isFieldFillable) {
                if ($isMenu) {
                    $value = Cli::prompt(self::CONSTANTS_QUESTIONS[$constant] . "\n", self::AVAILABLE_MENUS);
                } else {
                    $value = Cli::prompt(self::CONSTANTS_QUESTIONS[$constant] . "\n", $isRequired);
                }
            }

            if ($constant === 'TITLES_AND_BODIES_PER_LANGUAGE') {
                $value = $this->generateTitlesAndBodiesPerLanguage($seederType, $languages);
            } else {
                $value = $this->checkAndCorrectVariableType($value, $constant);
            }

            $constantsString .= "\t" . "protected const $constant = $value; \n";
        }

        return str_replace('CONSTANTS HERE', $constantsString, $commonContent);
    }

    private function generateTitlesAndBodiesPerLanguage(string $seederType, array $languages): string
    {
        $bodyType = self::SEEDERS_TYPES[$seederType]['bodyType'];
        $bodyExample = self::TITLES_AND_BODIES_EXAMPLES[$bodyType]['language'];
        $body = "[\n";

        if ($bodyType === 'lottery' || $bodyType === 'raffle') {
            foreach ($languages as $language) {
                $body .= "\t\t'$language' => [\n";

                foreach ($bodyExample as $site => $fieldWithValues) {
                    $body .= "\t\t\t'$site' => [ \n";
                    foreach ($fieldWithValues as $field => $value) {
                        $body .= "\t\t\t\t'$field' => '', \n";
                    }
                    $body .= "\t\t\t],\n";
                }
                $body .= "\t\t],\n";
            }
            $body .= "\t]";
            return $body;
        }

        if ($bodyType === 'navButton') {
            foreach ($languages as $language) {
                $body .= "\t\t'$language' => '', \n";
            }
            $body .= "\t]";
            return $body;
        }

        foreach ($languages as $language) {
            $body .= "\t\t'$language' => [\n";

            foreach ($bodyExample as $field => $value) {
                $body .= "\t\t\t'$field' => '', \n";
            }

            $body .= "\t\t],\n";
        }
        $body .= "\t]";

        return $body;
    }

    /**
     * @var mixed $value
     * @return mixed
     */
    private function checkAndCorrectVariableType($value, string $constant)
    {
        $isEmpty = empty($value);
        $isArrayConstant = $constant === 'WP_DOMAIN_NAME_WITHOUT_PORT';

        if (is_array($value)) {
            if ($isEmpty) {
                return '[]';
            }
            return $value;
        }
        if (is_bool($value)) {
            if ($value) {
                return 'true';
            }
            return 'false';
        }
        if (is_string($value)) {
            if ($isEmpty) {
                return "''";
            }

            if (str_contains($value, ',')) {
                $values = explode(',', $value);
                $valuesString = '';
                foreach ($values as $value) {
                    $valuesString .= "'$value'";
                    if (end($values) !== $value) {
                        $valuesString .= ",";
                    }
                }
                return "[$valuesString]";
            }

            if ($isArrayConstant) {
                return "['$value']";
            }

            return "'$value'";
        }
    }
}
