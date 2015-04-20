<?php

namespace MyBB\Parser\Smilies;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

class CachingDecorator implements ISmilieRepository
{
	/** @var ISmilieRepository */
	private $decoratedRepository;
	/** @var CacheRepository $cache */
	private $cache;

	/**
	 * @param ISmilieRepository $decorated
	 * @param CacheRepository   $cache
	 */
	public function __construct(ISmilieRepository $decorated, CacheRepository $cache)
	{
		$this->decoratedRepository = $decorated;
		$this->cache = $cache;
	}

	/**
	 * @return array
	 */
	public function getParsableSmilies()
	{
		if (($smilies = $this->cache->get('parser.smilies')) == null) {
			$smilies = $this->decoratedRepository->getParsableSmilies();
			$this->cache->forever('parser.smilies', $smilies);
		}

		return $smilies;
	}
}
