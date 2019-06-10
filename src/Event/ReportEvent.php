<?php

/*
 * This file is part of the doyo/behat-code-coverage project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doyo\Behat\Coverage\Event;

use Doyo\Behat\Coverage\Console\ConsoleIO;
use Doyo\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Symfony\Bridge\EventDispatcher\Event;

class ReportEvent extends Event
{
    const BEFORE_PROCESS = 'doyo.coverage.report_pre';
    const PROCESS        = 'doyo.coverage.report_process';
    const AFTER_PROCESS  = 'doyo.coverage.report_post';

    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @var ConsoleIO
     */
    private $consoleIO;

    public function __construct(ProcessorInterface $processor, ConsoleIO $consoleIO)
    {
        $this->processor = $processor;
        $this->consoleIO = $consoleIO;
    }

    /**
     * @return ProcessorInterface
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @return ConsoleIO
     */
    public function getConsoleIO(): ConsoleIO
    {
        return $this->consoleIO;
    }
}
