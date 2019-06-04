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
use Symfony\Component\EventDispatcher\Event;

class ReportEvent extends Event
{
    const BEFORE_PROCESS = 'doyo.coverage.report_pre';
    const PROCESS        = 'doyo.coverage.report_pre';
    const AFTER_PROCESS  = 'doyo.coverage.report_post';

    /**
     * @var CodeCoverage
     */
    private $coverage;

    public function getCoverage(): CodeCoverage
    {
        return $this->coverage;
    }

    public function setCoverage(CodeCoverage $coverage)
    {
        $this->coverage = $coverage;
    }
}
