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
     * @param CustomMyCodeRepositoryInterface $decorated
     * @param CacheRepository                 $cache
     */
    public function __construct(
        CustomMyCodeRepositoryInterface $decorated,
        CacheRepository $cache
    ) {
        $this->decoratedRepository = $decorated;
        $this->cache = $cache;
    }

    /**
     * Get all of the custom MyCodes, in the form [find => replace].
     *
     * @return array
     */
    public function getParseableCodes()
    {
        $cacheKey = 'parser.parseable_codes';

        // TODO: the cache doesn't work if more than one parser is used.
        // The cache should be named something like "parser.codes.[bbcode|markdown]"
        if (($myCodes = $this->cache->get($cacheKey)) === null) {
            $myCodes = $this->decoratedRepository->getParseableCodes();
            $this->cache->forever($cacheKey, $myCodes);
        }

        return $myCodes;
    }

    /**
     * Get all of the custom MyCodes.
     *
     * @return Collection
     */
    public function getAll()
    {
        $cacheKey = 'parser.mycodes_all';

        if (($myCodes = $this->cache->get($cacheKey)) === null) {
            $myCodes = $this->decoratedRepository->getAll();
            $this->cache->forever($cacheKey, $myCodes);
        }

        return $myCodes;
    }
}
