<?php
/**
 * Repository decorator to cache retrieved MyCode.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace Mybb\Parser\Database\Repositories\Decorators;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use MyBB\Parser\Database\Repositories\CustomMyCodeRepositoryInterface;

class CustomMyCodeCachingDecorator implements CustomMyCodeRepositoryInterface
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
     * @var Repository $config
     */
    private $config;

    /**
     * @param CustomMyCodeRepositoryInterface $decorated
     * @param CacheRepository $cache
     * @param Repository $config
     */
	public function __construct(
		CustomMyCodeRepositoryInterface $decorated,
		CacheRepository $cache,
        Repository $config
	) {
		$this->decoratedRepository = $decorated;
		$this->cache = $cache;
        $this->config = $config;
	}

	/**
	 * Get all of the custom MyCodes, in the form [find => replace].
	 *
	 * @return array
	 */
	public function getAllForParsing()
	{
		$cacheKey = 'parser.parseable_codes.' . $this->config->get('parser.formatter_type', 'mycode');

        return $this->cache->rememberForever($cacheKey, function() {
            return $this->decoratedRepository->getAllForParsing();
        });
    }

	/**
	 * Get all of the custom MyCodes.
	 *
	 * @return Collection
	 */
	public function getAll()
	{
		$cacheKey = 'parser.mycodes_all';

        return $this->cache->rememberForever($cacheKey, function() {
            return $this->decoratedRepository->getAll();
        });
	}
}
