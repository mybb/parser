<?php
/**
 * Repository decorator to cache retrieved bad words.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Database\Repositories\Decorators;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use MyBB\Parser\Database\Repositories\BadWordRepositoryInterface;

class BadWordCachingDecorator implements BadWordRepositoryInterface
{
    /**
     * @var BadWordRepositoryInterface $decorated
     */
    private $decorated;
    /**
     * @var CacheRepository $cache
     */
    private $cache;

    /**
     * @param BadWordRepositoryInterface $decorated
     * @param CacheRepository            $cache
     */
    public function __construct(
        BadWordRepositoryInterface $decorated,
        CacheRepository $cache
    ) {
        $this->decorated = $decorated;
        $this->cache = $cache;
    }

    /**
     * Get all bad words.
     *
     * @return Collection
     */
    public function getAll()
    {
        $cacheKey = 'parser.bad_words_all';

        if (($badWords = $this->cache->get($cacheKey)) === null) {
            $badWords = $this->decorated->getAll();
            $this->cache->forever($cacheKey, $badWords);
        }

        return $badWords;
    }

    /**
     * Get all of the defined bad words as an array ready for parsing.
     *
     * Bad words should be returned in the form [find => replace].
     *
     * @return array
     */
    public function getAllForParsing()
    {
        $cacheKey = 'parser.bad_words_all_for_parsing';

        if (($badWords = $this->cache->get($cacheKey)) === null) {
            $badWords = $this->decorated->getAllForParsing();
            $this->cache->forever($cacheKey, $badWords);
        }

        return $badWords;
    }
}
