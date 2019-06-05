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

namespace Doyo\Behat\Coverage\Event;

use Doyo\Behat\Coverage\Bridge\Aggregate;
use Doyo\Behat\Coverage\Bridge\Symfony\Event;

class CoverageEvent extends Event
{
    const BEFORE_START   = 'doyo.coverage.start.pre';
    const BEFORE_STOP    = 'doyo.coverage.stop.pre';
    const BEFORE_REFRESH = 'doyo.coverage.refresh.pre';
    const START          = 'doyo.coverage.start';
    const STOP           = 'doyo.coverage.stop';
    const REFRESH        = 'doyo.coverage.refresh';

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
     * @param string|null $coverageId
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
