<?php

/*
 * This file is part of the DoyoUserBundle project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doyo\Behat\Coverage\Bridge;

use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Remote\CoverageRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RemoteCoverage implements EventSubscriberInterface
{
    private $repository;

    public function __construct(
        CoverageRepository $repository
    ) {
        $this->repository = $repository;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoverageEvent::START   => 'onCoverageStarted',
            CoverageEvent::STOP    => 'onCoverageStopped',
            CoverageEvent::REFRESH => 'onCoverageRefresh',
        ];
    }

    public function onCoverageStarted()
    {
    }

    public function onCoverageStopped()
    {
    }

    public function onCoverageRefresh()
    {
    }
}
