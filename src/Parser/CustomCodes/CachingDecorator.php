<?php
/**
 * Cache custom codes
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/auth
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Parser\CustomCodes;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

class CachingDecorator implements CustomCodeRepositoryInterface
{
	/**
	 * @var CustomCodeRepositoryInterface
	 */
	private $decoratedRepository;
	/**
	 * @var CacheRepository
	 */
	private $cache;

	/**
	 * @param CustomCodeRepositoryInterface $decorated
	 * @param CacheRepository               $cache
	 */
	public function __construct(CustomCodeRepositoryInterface $decorated, CacheRepository $cache)
	{
		$this->decoratedRepository = $decorated;
		$this->cache = $cache;
	}

	/**
	 * @return array
	 */
	public function getParsableCodes()
	{
		// TODO: the cache doesn't work if more than one parser is used.
		// The cache should be named something like "parser.codes.[bbcode|markdown]"
		if (($smilies = $this->cache->get('parser.codes')) == null) {
			$smilies = $this->decoratedRepository->getParsableCodes();
			$this->cache->forever('parser.codes', $smilies);
		}

		return $smilies;
	}
}
