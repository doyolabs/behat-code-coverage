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

namespace Doyo\Behat\Coverage\Event;

use Doyo\Behat\Coverage\Bridge\Aggregate;
use Symfony\Component\EventDispatcher\Event;

class CoverageEvent extends Event
{
    const START   = 'doyo.coverage.start';
    const STOP    = 'doyo.coverage.stop';
    const REFRESH = 'doyo.coverage.refresh';

    /**
     * @var string
     */
    private $coverageId;

    /**
     * @var Aggregate
     */
    private $aggregate;

    public function __construct($coverageId = null)
    {
        $this->coverageId = $coverageId;
        $this->aggregate  = new Aggregate();
    }

    public function updateCoverage($coverage)
    {
        $aggregate = $this->aggregate;

        foreach ($coverage as $class => $counts) {
            $aggregate->update($class, $counts);
        }
    }

    /**
     * @return string
     */
    public function getCoverageId(): string
    {
        return $this->coverageId;
    }

    /**
     * @param null|string $coverageId
     */
    public function setCoverageId($coverageId=null)
    {
        $this->coverageId = $coverageId;
    }

    /**
     * @return Aggregate
     */
    public function getAggregate(): Aggregate
    {
        return $this->aggregate;
    }

    /**
     * @param Aggregate $aggregate
     */
    public function setAggregate(Aggregate $aggregate)
    {
        $this->aggregate = $aggregate;
    }
}
