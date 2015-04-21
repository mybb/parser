<?php

namespace MyBB\Parser\Badwords;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

class CachingDecorator implements BadwordRepositoryInterface
{
	/**
	 * @var BadwordRepositoryInterface
	 */
	private $decoratedRepository;
	/**
	 * @var CacheRepository
	 */
	private $cache;

	/**
	 * @param BadwordRepositoryInterface $decorated
	 * @param CacheRepository            $cache
	 */
	public function __construct(BadwordRepositoryInterface $decorated, CacheRepository $cache)
	{
		$this->decoratedRepository = $decorated;
		$this->cache = $cache;
	}

	/**
	 * @return array
	 */
	public function getAllAsArray()
	{
		if (($badwords = $this->cache->get('parser.badwords')) == null) {
			$badwords = $this->decoratedRepository->getAllAsArray();
			$this->cache->forever('parser.badwords', $badwords);
		}

		return $badwords;
	}
}
