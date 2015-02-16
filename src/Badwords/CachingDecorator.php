<?php

namespace MyBB\Parser\Badwords;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

class CachingDecorator implements IBadwordRepository
{
    /** @var IBadwordRepository */
    private $decoratedRepository;
    /** @var CacheRepository $cache */
    private $cache;

    /**
     * @param IBadwordRepository $decorated
     * @param CacheRepository    $cache
     */
    public function __construct(IBadwordRepository $decorated, CacheRepository $cache)
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
