<?php
/**
 * Repository decorator to cache retrieved smileys.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace Mybb\Parser\Database\Repositories\Decorators;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use Mybb\Parser\Database\Repositories\SmileyRepositoryInterface;

class SmileysCachingDecorator implements SmileyRepositoryInterface
{
	/**
	 * @var SmileyRepositoryInterface
	 */
	private $decoratedRepository;
	/**
	 * @var CacheRepository
	 */
	private $cache;

	/**
	 * @param SmileyRepositoryInterface $decorated
	 * @param CacheRepository           $cache
	 */
	public function __construct(
		SmileyRepositoryInterface $decorated,
		CacheRepository $cache
	) {
		$this->decoratedRepository = $decorated;
		$this->cache = $cache;
	}

	/**
	 * @return array
	 */
	public function getParseableSmileys()
	{
		if (($smileys = $this->cache->get(
				'parser.parseable_smileys'
			)) == null
		) {
			$smileys = $this->decoratedRepository->getParseableSmileys();
			$this->cache->forever('parser.parseable_smileys', $smileys);
		}

		return $smileys;
	}

	/**
	 * Get all defined smileys.
	 *
	 * @return Collection
	 */
	public function getAll()
	{
		if (($smileys = $this->cache->get('parser.all_smileys')) === null) {
			$smileys = $this->decoratedRepository->getAll();

			$this->cache->forever('parser.all_smileys', $smileys);
		}

		return $smileys;
	}
}
