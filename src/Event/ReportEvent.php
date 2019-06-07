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

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Doyo\Behat\Coverage\Bridge\Symfony\Event;
use Symfony\Component\Console\Style\StyleInterface;

class ReportEvent extends Event
{
    const BEFORE_PROCESS = 'doyo.coverage.report_pre';
    const PROCESS        = 'doyo.coverage.report_process';
    const AFTER_PROCESS  = 'doyo.coverage.report_post';

    /**
     * @var Processor|null
     */
    private $processor;

    /**
     * @var StyleInterface|null
     */
    private $io;

    /**
     * @var \Exception[]
     */
    private $exceptions = [];

    /**
     * @return Processor|null
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @param Processor $processor
     *
     * @return static
     */
    public function setProcessor(Processor $processor)
    {
        $this->processor = $processor;

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
     * @return static
     */
    public function setIO(StyleInterface $io)
    {
        $this->io = $io;

        return $this;
    }

    public function addException(\Exception $exception)
    {
        $this->exceptions[] = $exception;
    }

    /**
     * @return \Exception[]
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }
}
