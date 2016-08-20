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
use Illuminate\Support\Collection;
use MyBB\Parser\Database\Repositories\CustomMyCodeRepositoryInterface;

class CustomMyCodeCachingDecorator implements CustomMyCodeRepositoryInterface
{
    private const PARSEABLE_CODES_KEY = 'parser.parseable_codes';

    private const ALL_CODES_KEY = 'parser.mycodes_all';

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
     * Get all of the custom MyCodes, in the form [find => replace].
     *
     * @return array
     */
    public function getAllForParsing(): array
    {
        return $this->cache->rememberForever(static::PARSEABLE_CODES_KEY, function () {
            return $this->decoratedRepository->getAllForParsing();
        });
    }

    /**
     * Get all of the custom MyCodes.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->cache->rememberForever(static::ALL_CODES_KEY, function () {
            return $this->decoratedRepository->getAll();
        });
    }
}
