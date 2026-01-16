<?php

namespace Fuel\Tasks;

use Fuel\Core\Cli;
use Fuel\Core\Lang;
use Gettext\Loader\PoLoader;
use LanguageHelper;
use Task_Cli;
use Throwable;

/**
 * php oil r refresh_plugins_translation
 * Scans gettext phrases for translations
 *
 * php oil r refresh_plugins_translation:generateUntranslated
 * Generate files with empty translations
 *
 * php oil r refresh_plugins_translation:removeUntraslated
 * Deletes files with empty translations
 *
 * php oil r refresh_plugins_translation:compileMo
 * Generate .mo files from .po files
 *
 * php oil r refresh_plugins_translation:untranslatedPoToXls
 * Generate .xlsx files from empty translations
 *
 * php oil r refresh_plugins_translation:updateXlsToPo
 * Updates .po translation files from .xlsx files
 */
class Refresh_Plugins_Translation extends Task_Cli
{
    private string $message =
        "You must have:\n" .
        "1. sudo apt-get install -y gettext\n" .
        "2. sudo apt install python3-pip\n" .
        "3. pip install poxls\n" .
        "4. sudo apt-get install translate-toolkit\n" .
        "5. sudo apt-get install gnumeric\n";

    public function __construct()
    {
        $this->disableOnProduction();
    }

    public function help(): void
    {
        echo "Commands in examples:\n" .
            "1. php oil r refresh_plugins_translation\n" .
            "2. php oil r refresh_plugins_translation:generateUntranslated\n" .
            "3. php oil r refresh_plugins_translation:removeUntranslated\n" .
            "4. php oil r refresh_plugins_translation:compileMo\n" .
            "5. php oil r refresh_plugins_translation:untranslatedPoToXls\n" .
            "6. php oil r refresh_plugins_translation:updateXlsToPo\n" .
            "7. php oil r refresh_plugins_translation:generateFuelTranslations\n\n";
        echo $this->message;
    }

    /**
     * Fuel does not allow to use .po files as translations so we have to generate it as array
     * This method was mainly created for validation message issues.
     * Use translations in .po format where possible
     */
    public function generateFuelTranslations(): void
    {
        $files = [
            'validation' => APPPATH . '/lang/FuelTranslations/ValidationErrors.php'
        ];

        foreach (LanguageHelper::LOCALES as $locale) {
            $localeArray = explode('_', $locale);
            $language = $localeArray[0];
            $loader = new PoLoader();
            $translationsPo = $loader->loadFile("/var/www/wordpress/wp-content/plugins/lotto-platform/languages/gettext/$locale/LC_MESSAGES/lotto-platform.po");

            foreach ($files as $group => $file) {
                $translations = include $file;
                $translations = array_keys($translations);
                foreach ($translations as $translationKey) {
                    Lang::set_lang($language);
                    $translation = $translationsPo->find(null, $translationKey);
                    if ($translation) {
                        Lang::set($translationKey, rtrim($translation->getTranslation()), $group, $language);
                    }
                }

                try {
                    Lang::save($group, Lang::get($group), $language);
                } catch (Throwable $e) {
                }
            }
            echo "$language, ";
        }

        echo "\n Finished \n";
    }

    public function run(): void
    {
        Refresh_Plugins_Translation::checkGetTextIsInstalled();

        foreach (LanguageHelper::LOCALES as $locale) {
            echo $locale . " ";
            try {
                shell_exec("
                cd .. &&
                cd wordpress/wp-content/plugins/lotto-platform/languages/gettext/$locale/LC_MESSAGES/ &&
                find  ../../../../../../../../wordpress ../../../../../../../../platform -type f \( -name '*.php' -or -name '*.phtml' \)  -print > list &&
                sed -i '/platform\/fuel\/app\/classes\/forms\/whitelabel/d' list
                sed -i '/platform\/fuel\/app\/classes\/forms\/aff/d' list
                sed -i '/platform\/fuel\/app\/classes\/forms\/admin/d' list
                sed -i '/platform\/fuel\/app\/views\/admin/d' list
                sed -i '/platform\/fuel\/app\/views\/aff/d' list
                sed -i '/platform\/fuel\/app\/views\/whitelabel/d' list
                sed -i '/platform\/fuel\/app\/classes\/presenter\/traits/d' list
                sed -i '/platform\/fuel\/app\/classes\/presenter\/admin/d' list
                sed -i '/platform\/fuel\/app\/classes\/presenter\/aff/d' list
                sed -i '/platform\/fuel\/app\/classes\/presenter\/whitelabel/d' list
                sed -i '/platform\/fuel\/app\/classes\/validators\/manager/d' list
                sed -i '/platform\/fuel\/app\/classes\/controller\/admin.php/d' list
                sed -i '/platform\/fuel\/app\/classes\/controller\/aff.php/d' list
                sed -i '/platform\/fuel\/app\/classes\/controller\/crm.php/d' list
                sed -i '/platform\/fuel\/app\/classes\/controller\/whitelabel.php/d' list
                sed -i '/platform\/fuel\/app\/classes\/controller\/aff/d' list
                sed -i '/platform\/fuel\/app\/classes\/helpers\/aff/d' list
                sed -i '/platform\/fuel\/app\/classes\/helpers\/crm/d' list
                sed -i '/platform\/fuel\/app\/classes\/helpers\/empire/d' list
                sed -i '/platform\/fuel\/app\/classes\/helpers\/whitelabel/d' list
                sed -i '/platform\/fuel\/app\/classes\/validator\/whitelabel/d' list
                sed -i '/resources\/[a-z]*.custom.options.view.php/d' list
                sed -i '/Payments\/AbstractPaymentCustomOptionsValidation.php/d' list
                sed -i '/platform\/fuel\/app\/classes\/traits\/prepare/d' list
                mv lotto-platform.po messages.po &&
                xgettext --files-from=list --language=PHP -j messages.po &&
                sed -i -e '1,6d' messages.po &&
                sed -i -e '6,8d' messages.po &&
                sed -i -e '4d' messages.po &&
                sed -i -e '5s/Language: /Language: $locale/' messages.po &&
                sed -i -e 's/PACKAGE VERSION/WhiteLotto - Lotto Platform plugin/' messages.po &&
                sed -i '9i \"X-Poedit-SourceCharset: UTF-8\\\\n\"' messages.po &&
                sed -i '10i \"X-Poedit-Basepath: ../../../../../../../..\\\\n\"' messages.po &&
                sed -i '11i \"X-Poedit-SearchPath-0: wordpress/\\\\n\"' messages.po &&
                sed -i '12i \"X-Poedit-SearchPath-1: platform/\\\\n\"' messages.po &&
                mv messages.po lotto-platform.po &&
                rm -f list
            ");
            } catch (Throwable $e) {
                $e->getMessage() . "\n";
                $this->message;
            }
        }
    }

    public function generateUntranslated(): void
    {
        foreach (LanguageHelper::LOCALES as $locale) {
            echo $locale . " ";
            try {
                shell_exec("
                cd .. &&
                cd wordpress/wp-content/plugins/lotto-platform/languages/gettext/$locale/LC_MESSAGES/ &&
                cp lotto-platform.po $locale.po
                msgattrib --untranslated lotto-platform.po -o $locale.po
            ");
            } catch (Throwable $e) {
                $e->getMessage() . "\n";
                "You must have gettext\n";
                "sudo apt-get install -y gettext\n";
            }
        }
        echo "\nRemember to delete these files with option \nremove_untraslated \n";
    }

    public function removeUntranslated(): void
    {
        foreach (LanguageHelper::LOCALES as $locale) {
            echo $locale . " ";
            try {
                shell_exec("
                cd .. &&
                cd wordpress/wp-content/plugins/lotto-platform/languages/gettext/$locale/LC_MESSAGES/ &&
                rm -f $locale.po
            ");
            } catch (Throwable $e) {
                $e->getMessage() . "\n";
                "You must have gettext\n";
                "sudo apt-get install -y gettext\n";
            }
        }
    }

    public function compileMo(): void
    {
        foreach (LanguageHelper::LOCALES as $locale) {
            echo $locale . " ";
            try {
                shell_exec("
                cd .. &&
                cd wordpress/wp-content/plugins/lotto-platform/languages/gettext/$locale/LC_MESSAGES/ &&
                msgfmt -o lotto-platform.mo lotto-platform.po
            ");
            } catch (Throwable $e) {
                $e->getMessage() . "\n";
                "You must have gettext\n";
                "sudo apt-get install -y gettext\n";
            }
        }
    }

    public function untranslatedPoToXls(): void
    {
        foreach (LanguageHelper::LOCALES as $locale) {
            echo $locale . " ";
            try {
                shell_exec("
                cd ../wordpress/wp-content/plugins/lotto-platform/languages/gettext/$locale/LC_MESSAGES &&
                po-to-xls -o $locale.xlsx $locale.po
            ");
            } catch (Throwable $e) {
                $e->getMessage() . "\n";
                "You must have gettext\n";
                "sudo apt-get install -y gettext\n";
            }
        }
    }

    public function updateXlsToPo(): void
    {
        foreach (LanguageHelper::LOCALES as $locale) {
            echo $locale . " ";
            try {
                shell_exec("
                cd ../wordpress/wp-content/plugins/lotto-platform/languages/gettext/$locale/LC_MESSAGES &&
                ssconvert $locale.xlsx file.csv &&
                csv2po file.csv file.po &&
                sed -i -e '1,3d' file.po &&
                sed -i '/msgstr \"\"/d' file.po &&
                sed -i 's/msgid/msgstr/g' file.po &&
                sed -i 's/msgctxt/msgid/g' file.po &&
                sed -i '1i msgid \"\"' file.po &&
                sed -i '2i msgstr \"\"' file.po &&
                sed -i -e '13,15d' file.po &&
                msgcat lotto-platform.po file.po -o merge.po --use-first &&
                rm -f lotto-platform.po &&
                mv merge.po lotto-platform.po &&
                rm -f file.po &&
                rm -f file.csv &&
                rm -f $locale.xlsx &&
                rm -f $locale.po
            ");
            } catch (Throwable $e) {
                $e->getMessage() . "\n";
                $this->message;
            }
        }
    }

    /**
     * Warning! This task assumes that en_GB/lotto-platform.po is already prepared, as base file.
     * This means: the words that are NOT in this file will be removed from translated language .po files.
     * Anything else that was previously translated, will remain translated.
     */
    public function removeUnused(): void
    {
        Refresh_Plugins_Translation::checkGetTextIsInstalled();

        $locales = LanguageHelper::LOCALES;
        $englishKey = array_search('en_GB', $locales);
        unset($locales[$englishKey]);

        try {
            shell_exec("
                cd .. &&
                cd wordpress/wp-content/plugins/lotto-platform/languages/gettext/en_GB/LC_MESSAGES/ &&
                cp lotto-platform.po messages_base.pot
            ");
        } catch (Throwable $exception) {
            echo "Could not copy base file: " . $exception->getMessage();
        }

        foreach ($locales as $locale) {
            echo $locale . " ";
            try {
                shell_exec("
                cd .. &&
                cd wordpress/wp-content/plugins/lotto-platform/languages/gettext/$locale/LC_MESSAGES/ &&
                mv lotto-platform.po messages.po &&
                msgattrib --set-obsolete --ignore-file=/var/www/wordpress/wp-content/plugins/lotto-platform/languages/gettext/en_GB/LC_MESSAGES/messages_base.pot -o messages.po messages.po &&
                msgattrib --no-obsolete -o messages.po messages.po &&
                mv messages.po lotto-platform.po
            ");
            } catch (Throwable $exception) {
                echo "Removing unused strings for $locale has failed: " . $exception->getMessage();
            }
        }

        try {
            shell_exec("
                cd .. &&
                cd wordpress/wp-content/plugins/lotto-platform/languages/gettext/en_GB/LC_MESSAGES/ &&
                rm -f messages_base.pot
            ");
        } catch (Throwable $exception) {
            echo "Could not remove base file: " . $exception->getMessage();
        }
    }

    /** Running commands without gettext installed might corrupt files */
    private static function checkGetTextIsInstalled(): void
    {
        $isGetTextInstalled = shell_exec("dpkg -s gettext | grep 'install ok installed'");
        if (empty($isGetTextInstalled)) {
            $shouldInstall = Cli::prompt("Do you want to install gettext? \n", ['y', 'n']);
            if ($shouldInstall === "y") {
                echo shell_exec('sudo apt-get install -y gettext');
            } else {
                die;
            }
        }
    }
}
