<?php
/**
 * Repository decorator to cache retrieved MyCode.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Database\Repositories\Decorators;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use MyBB\Parser\Database\Repositories\CustomMyCodeRepositoryInterface;

class CustomMyMyCodeCachingDecorator implements CustomMyCodeRepositoryInterface
{
	/**
	 * @var CustomMyCodeRepositoryInterface
	 */
	private $decoratedRepository;
	/**
	 * @var CacheRepository
	 */
	private $cache;

	/**
	 * @param CustomMyCodeRepositoryInterface $decorated
	 * @param CacheRepository                 $cache
	 */
	public function __construct(CustomMyCodeRepositoryInterface $decorated, CacheRepository $cache)
	{
		$this->decoratedRepository = $decorated;
		$this->cache = $cache;
	}

	/**
	 * @return array
	 */
	public function getParseableCodes()
	{
		// TODO: the cache doesn't work if more than one parser is used.
		// The cache should be named something like "parser.codes.[bbcode|markdown]"
		if (($smilies = $this->cache->get('parser.codes')) == null) {
			$smilies = $this->decoratedRepository->getParseableCodes();
			$this->cache->forever('parser.codes', $smilies);
		}

		return $smilies;
	}
}
