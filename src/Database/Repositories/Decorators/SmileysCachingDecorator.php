<?php
/**
 * Repository decorator to cache retrieved smileys.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Database\Repositories\Decorators;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use MyBB\Parser\Database\Repositories\SmileyRepositoryInterface;

class SmileysCachingDecorator implements SmileyRepositoryInterface
{
    const ALL_SMILEYS_FOR_PARSING_KEY = 'parser.parseable_smileys';

    const ALL_SMILEYS_KEY = 'parser.all_smileys';

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
    public function __construct(SmileyRepositoryInterface $decorated, CacheRepository $cache)
    {
        $this->decoratedRepository = $decorated;
        $this->cache = $cache;
    }

    /**
     * @return array
     */
    public function getAllForParsing(): array
    {
        return $this->cache->rememberForever(static::ALL_SMILEYS_FOR_PARSING_KEY, function () {
            return $this->decoratedRepository->getAllForParsing();
        });
    }

    /**
     * Get all defined smileys.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->cache->rememberForever(static::ALL_SMILEYS_KEY, function () {
            return $this->decoratedRepository->getAll();
        });
    }
}
