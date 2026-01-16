<?php

namespace Fuel\Tasks;

use DOMDocument;
use DOMElement;
use Fuel\Core\Cli;
use InvalidArgumentException;
use Task_Cli;

use const JSON_THROW_ON_ERROR;
use const PHP_EOL;

/**
 * Class Phpcs
 * This is a helper command that helps to ignore common "Fuel" linting errors.
 * Each of below listed rule will be added to phpcs.xml exclude rule.
 */
final class Phpcs extends Task_Cli
{
    private const RULES = [
        'PSR1.Files.SideEffects.FoundWithSymbols',
        'PEAR.Functions.ValidDefaultValue.NotAtEnd',
        'PSR2.Methods.MethodDeclaration.Underscore',
        'PSR1.Methods.CamelCapsMethodName.NotCamelCaps',
        'PSR1.Files.SideEffects.FoundWithSymbols',
        'PSR1.Classes.ClassDeclaration.MissingNamespace',
        'PSR2.Classes.PropertyDeclaration.Underscore',
        'Generic.Files.LineLength.MaxExceeded',
        'PSR1.Classes.ClassDeclaration.MultipleClasses',
        'Squiz.Scope.MethodScope.Missing',
        'Squiz.Classes.ValidClassName.NotCamelCaps',
    ];

    private const OUTPUT = __DIR__ . '/../../../../var/output.json';
    private const CONFIG_FILE = __DIR__ . '/../../../../phpcs.xml';

    private const COMMAND = 'php ./fuel/vendor/bin/phpcs --standard=' . self::CONFIG_FILE . ' -s --report=json --report-file=' . self::OUTPUT . ' --encoding=UTF-8';

    /** @var array<string, string> - key - RULES.name, value - compiled exclude rule */
    private array $excludes = [];

    public function __construct()
    {
        $this->disableOnProduction();
    }

    public function run(): void
    {
        Cli::write('Running auto fix for Fuel PHPCS, it can take about 1 minute ...' . PHP_EOL);

        $result = shell_exec(self::COMMAND);
        Cli::write($result);

        Cli::write('Running auto fix for Fuel PHPCS' . PHP_EOL);

        sleep(1);

        $json = json_decode(file_get_contents(self::OUTPUT), true, JSON_THROW_ON_ERROR);

        foreach ($json['files'] as $path => $values) {
            foreach ($values['messages'] as $msg) {
                foreach (self::RULES as $rule) {
                    if (!empty($msg['source']) && $msg['source'] === $rule) {
                        $path = str_replace('/var/www/', '', $path);
                        $this->excludes[$rule] = $path;
                    }
                }
            }
        }

        if (empty($this->excludes)) {
            Cli::write('There is no data to be auto fixed');
        }

        $dom = new DOMDocument();
        $dom->load(self::CONFIG_FILE);

        $root = $dom->documentElement;

        foreach ($this->excludes as $ruleName => $path) {
            $ruleSelector = '[ref="' . $ruleName . '"]';
            $rules = $root->getElementsByTagName('rule');
            if (empty($rules->count())) {
                throw new InvalidArgumentException(sprintf('No rule found %s', $ruleSelector));
            }

            /** @var DOMElement $rule */
            foreach ($rules as $rule) {
                if ($rule->getAttribute('ref') !== $ruleName) {
                    continue;
                }
                $new = $dom->createElement('exclude-pattern', $path);
                $rule->appendChild($new);
            }
        }

        $dom->saveXML();
        $dom->save(self::CONFIG_FILE);

        Cli::write(
            self::CONFIG_FILE .
            ' has been modified! Review all new entries ' .
            'and remove these file that should work with PSR-12.'
        );
    }
}
