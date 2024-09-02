<?php

namespace App\Service;

use Psr\Cache\CacheException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Cache\CacheItem;

class CacheService extends TagAwareAdapter
{
    public function __construct(string $dsn)
    {
        $connection = RedisAdapter::createConnection($dsn);

        parent::__construct(new TagAwareAdapter(new RedisAdapter($connection)));
    }

    /**
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function markQuestionCache(CacheItem $item): void
    {
        $item->tag('questions');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function resetQuestionCaches(): void
    {
        $this->invalidateTags(['questions']);
    }
}