<?php

namespace MyBB\Parser\Parser\CustomCodes;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

class CachingDecorator implements ICustomCodeRepository
{
	/** @var ICustomCodeRepository */
	private $decoratedRepository;
	/** @var CacheRepository $cache */
	private $cache;

	/**
	 * @param ICustomCodeRepository $decorated
	 * @param CacheRepository       $cache
	 */
	public function __construct(ICustomCodeRepository $decorated, CacheRepository $cache)
	{
		$this->decoratedRepository = $decorated;
		$this->cache = $cache;
	}

	/**
	 * @return array
	 */
	public function getParsableCodes()
	{
		// TODO: the cache doesn't work if more than one parser is used. The cache should be named something like "parser.codes.[bbcode|markdown]"
		if (($smilies = $this->cache->get('parser.codes')) == null) {
			$smilies = $this->decoratedRepository->getParsableCodes();
			$this->cache->forever('parser.codes', $smilies);
		}

		return $smilies;
	}
}
