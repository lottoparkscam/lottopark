<?php

namespace Services\Logs;

use Carbon\Carbon;
use Throwable;
use Model_Model;
use Classes\Orm\AbstractOrmModel;
use Helpers\UrlHelper;
use Helpers\StringHelper;
use Wrappers\Decorators\ConfigContract;
use Helpers_Time;
use Exception;
use Helpers\ArrayHelper;

/** Log file path example: /var/log/php/whitelotto/year/month/day.log */
abstract class AbstractLoggerService
{
    public string $source = 'default';
    protected string $debugFilePath;
    protected int $triggeredInLine;
    protected array $calledFunctionArguments;
    protected Carbon $now;
    protected array $wholeDebugBacktrace = [];
    protected ConfigContract $configContract;
    protected LogObject $log;

    public function __construct(ConfigContract $configContract, LogObject $log)
    {
        $this->configContract = $configContract;
        $this->now = Carbon::now();
        $this->log = $log;
    }

    /**
     * @param array $debugBackTrace has to be provided from children, 
     * called here changes debugBackTrace to children and not to called place.
     */
    public function configure(array $debugBackTrace = []): void
    {
        $debugBackTrace = !empty($debugBackTrace) ? $debugBackTrace : (new Exception())->getTrace();
        $this->configureDebugBackTrace($debugBackTrace);
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }
    
    /**
     * $debugBackTrace[0] has different content if function was called from Container.
     * @param array $debugBackTrace[0] contains of called function place and details
     */
    private function configureDebugBackTrace(array $debugBackTrace): void
    {
        $dependencyInjectionFiles = [
            'php-di/php-di/src/Definition/Resolver/ObjectCreator.php',
            'php-di/php-di/src/Definition/Resolver/ResolverDispatcher.php',
            'php-di/php-di/src/Container.php',
            'app/classes/container.php'
        ];

        $isCalledFromContainer = isset($debugBackTrace[1]['file']) && str_contains($debugBackTrace[1]['file'], 'php-di/php-di/src/Definition/Resolver/ObjectCreator.php');
        if ($isCalledFromContainer) {
            $debugBackTrace = ArrayHelper::deleteValuesForMultiDimensionalArray($debugBackTrace, $dependencyInjectionFiles, true, true, 'file');
        }

        $this->debugFilePath = $debugBackTrace[0]['file'];
        $this->triggeredInLine = $debugBackTrace[0]['line'];
        $this->calledFunctionArguments = $debugBackTrace[0]['args'] ?? [];
        $this->wholeDebugBacktrace = $debugBackTrace;
    }
    
    public function getBackTraceAsString(): string
    {
        $traces = '';

        foreach ($this->wholeDebugBacktrace as $number => $trace) {
            $isCalledWithArgs = !empty($trace['args']);

            $args = [];
            if ($isCalledWithArgs) {
                foreach ($trace['args'] as $key => $arg) {
                    $isObjectModel = $arg instanceof AbstractOrmModel || $arg instanceof Model_Model;
                    $isArrayModel = is_array($arg) && isset($arg['id']);
                    if ($isArrayModel) {
                        $arg = 'Found sensitive data from model. Provided model with id: ' . $arg['id'] ?? 'hidden id';
                    }
                    if ($isObjectModel) {
                        $arg = 'Found sensitive data from model: ' . get_class($arg) . ' Provided model with id: ' . $arg->id ?? 'hidden id';
                    }
                    if (is_object($arg)) {
                        $arg = get_class((object)$this->wholeDebugBacktrace[$number]['args'][$key]);
                    }

                    try {
                        if (is_array($arg)) {
                            $arg = json_encode($arg);
                        }
                    } catch (Throwable $e) {
                        $arg = null;
                    }

                    if (!empty($arg)) {
                        $args[] = $arg;
                    }
                }
            }

            $functionCalledWithArgs = !empty($args) ? implode(', ', $args) : '';
            $functionCalledWithArgs = str_replace(PHP_EOL, '', $functionCalledWithArgs);
            $functionCalledWithArgs = str_replace('`', '"', $functionCalledWithArgs);
            $line = $trace['line'] ?? '';
            $file = $trace['file'] ?? '';
            $class = $trace['class'] ?? '';
            $type = $trace['type'] ?? '';

            $number++;
            $traces .= <<<TRACE
            #{$number} function: {$class}{$type}{$trace['function']}({$functionCalledWithArgs}), {$file}:{$line} \r
            TRACE;

            $shouldReturnShortenTraces = strlen($traces) > 3000;
            if ($shouldReturnShortenTraces) {
                return $traces;
            }
        }

        return $traces;
    }

    protected function prepareUrlForLog(string $message, string $exportDebugBacktrace): string
    {
        $urlError = 'Cannot find url. Misconfiguration or triggered from CLI.';
        $tasksPath = 'app/tasks/';
        $taskPath = 'app/classes/task/'; // old tasks (mainly lcs)

        // Fuel has 2 ways to store file path
        // 1. For fatal errors it stores inside the log message
        // 2. If not fatal error it stores inside backtrace 
        $isTaskNameInMessage = str_contains($message, $tasksPath);
        $isTaskNameInPath = str_contains($exportDebugBacktrace, $tasksPath) || str_contains($exportDebugBacktrace, $taskPath);

        if ($isTaskNameInMessage) {
            $taskLocation = StringHelper::getStringAfterSubString($message, $tasksPath);
            // we change syntax in order to have path friendlier IDE navigation
            $url = str_replace(' on line ', ':', $taskLocation);
            return !empty($url) ? $url : $urlError;
        }

        if ($isTaskNameInPath) {
            return 'Path below in *file* header.';
        }

        $url = UrlHelper::getCurrentUrlWithParams(false);

        return !empty($url) ? $url : $urlError;
    }

    public function prepareLogLine(string $message, string $type, string $source): LogObject
    {
        $message = str_replace("\n", ' ', $message);
        $exportDebugBacktrace = $this->getBackTraceAsString();
        $url = $this->prepareUrlForLog($message, $exportDebugBacktrace);
        
        $timestampWithTimezone = Helpers_Time::getTimestampWithTimezone($this->now);
        $source = strtoupper($source) . ' & ' . strtoupper($this->source);

        $debugFilePath = $this->debugFilePath;
        $triggeredInLine = $this->triggeredInLine;

        $logDetails = json_encode([
            'type' => $type,
            'source' => $source,
            'date' => $timestampWithTimezone,
            'url' => $url,
            'message' => $message,
            'file' => "{$debugFilePath}:{$triggeredInLine}",
            'trace' => $exportDebugBacktrace
        ]);

        $logDetailsJson = json_decode($logDetails);
        $this->log = new $this->log($logDetailsJson);

        return $this->log;
    }
}
