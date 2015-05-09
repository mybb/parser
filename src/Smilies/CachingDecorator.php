<?php
/**
 * Cache smilies
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Smilies;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

class CachingDecorator implements SmilieRepositoryInterface
{
	/**
	 * @var SmilieRepositoryInterface
	 */
	private $decoratedRepository;
	/**
	 * @var CacheRepository
	 */
	private $cache;

	/**
	 * @param SmilieRepositoryInterface $decorated
	 * @param CacheRepository           $cache
	 */
	public function __construct(SmilieRepositoryInterface $decorated, CacheRepository $cache)
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
