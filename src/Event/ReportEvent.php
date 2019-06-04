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

use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\EventDispatcher\Event;

class ReportEvent extends Event
{
    const BEFORE_PROCESS = 'doyo.coverage.report_pre';
    const PROCESS        = 'doyo.coverage.report_process';
    const AFTER_PROCESS  = 'doyo.coverage.report_post';

    /**
     * @var CodeCoverage|null
     */
    private $coverage;

    /**
     * @var StyleInterface|null
     */
    private $io;

    /**
     * @return CodeCoverage|null
     */
    public function getCoverage()
    {
        return $this->coverage;
    }

    /**
     * @param CodeCoverage $coverage
     */
    public function setCoverage(CodeCoverage $coverage)
    {
        $this->coverage = $coverage;

        return $this;
    }

    /**
     * @return StyleInterface|null
     */
    public function getIO()
    {
        return $this->io;
    }

    /**
     * @param StyleInterface|null $io
     *
     * @return ReportEvent
     */
    public function setIO(StyleInterface $io)
    {
        $this->io = $io;

        return $this;
    }
}
