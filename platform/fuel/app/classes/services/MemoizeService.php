<?php

namespace Services;

/**
 * Life time cache
 * Use only in classes
 */
class MemoizeService
{
	private array $cache;
	private array $activeArgs;
	private string $activeCacheName;

	public function prepareArgs(...$args): void
	{
		$this->activeArgs = $args;
		$caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
		$methodName = $caller['function'];
		$className = $caller['class'];
		$this->activeCacheName = "{$methodName}_{$className}";
	}

	public function findCachedResult()
	{
		$cacheNotExists = empty($this->cache[$this->activeCacheName]);

		if ($cacheNotExists) {
			return null;
		}

		$argsAreTheSame = $this->cache[$this->activeCacheName]['args'] !== $this->activeArgs;
		if ($argsAreTheSame) {
			return null;
		}

		return $this->cache[$this->activeCacheName]['result'];
	}

	public function addResultToCache($result): void
	{
		$this->cache[$this->activeCacheName] = [
			'args' => $this->activeArgs,
			'result' => $result
		];
	}
}