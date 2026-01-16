<?php

use Fuel\Core\Cli;

/**
 * Helper for command line.
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2020-03-27
 * Time: 11:50:57
 */
final class Helpers_Cli
{

    const INFO_COLOR = 'white';
    const INFO_BACKGROUND = 'blue';
    const SUCCESS_COLOR = 'white';
    const SUCCESS_BACKGROUND = 'green';
    const WARNING_COLOR = 'black';
    const WARNING_BACKGROUND = 'yellow';
    const ERROR_COLOR = 'white';
    const ERROR_BACKGROUND = 'red';

    public static function execute_or_fail_with_print_output(string $command): void
    {
        $proc = proc_open($command, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
        $has_errors = false;
        while (($line = fgets($pipes[1])) !== false) {
            fwrite(STDOUT, $line);
        }
        while (($line = fgets($pipes[2])) !== false) {
            fwrite(STDERR, $line);
            $has_errors = true;
        }
        foreach ($pipes as &$pipe){
            fclose($pipe);
        }
        proc_close($proc);
        if ($has_errors) {
            exit;
        }
    }

    public static function writeln(string $text): void
    {
        Cli::write($text);
    }

    public static function info(string $text)
    {
        Cli::write( "[info] " . $text, self::INFO_COLOR, self::INFO_BACKGROUND);
        Cli::new_line();
    }

    public static function success(string $text)
    {
        Cli::write("ʕ•ᴥ•ʔ " . $text, self::SUCCESS_COLOR, self::SUCCESS_BACKGROUND);
        Cli::new_line();
    }

    public static function warning(string $text)
    {
        Cli::write("[warning] " . $text, self::WARNING_COLOR, self::WARNING_BACKGROUND);
        Cli::new_line();
    }

    public static function error(string $text)
    {
        Cli::write("[error] " . $text, self::ERROR_COLOR, self::ERROR_BACKGROUND);
        Cli::new_line();
    }
}
