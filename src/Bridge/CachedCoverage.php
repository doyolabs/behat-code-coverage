<?php

/*
 * This file is part of the doyo/behat-coverage-extension project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doyo\Behat\Coverage\Bridge;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Cache;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use SebastianBergmann\CodeCoverage\Filter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CachedCoverage implements EventSubscriberInterface
{
    /**
     * @var Cache
     */
    private $cache;

    public function __construct($namespace, array $codeCoverageOptions, Filter $filter)
    {
        $cache = new Cache($namespace);

        $cache->setCodeCoverageOptions($codeCoverageOptions);
        $cache->setFilter($filter);
        $cache->save();

        $this->cache = $cache;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoverageEvent::REFRESH => 'onCoverageRefresh',
            CoverageEvent::START   => 'onCoverageStarted',
            CoverageEvent::STOP    => ['onCoverageStopped', 10],
        ];
    }

    /**
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param Cache $cache
     *
     * @return CachedCoverage
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    public function onCoverageRefresh()
    {
        $this->cache->reset();
    }

    public function onCoverageStarted(CoverageEvent $event)
    {
        $cache = $this->cache;

        $cache->setCoverageId($event->getCoverageId());
        $cache->save();
    }

    public function onCoverageStopped(CoverageEvent $event)
    {
        $cache = $this->cache;

        $cache->readCache();
        $event->updateCoverage($cache->getCoverage());
    }
}
