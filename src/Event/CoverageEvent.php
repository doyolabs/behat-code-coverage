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
use Doyo\Bridge\CodeCoverage\TestCase;
use Doyo\Symfony\Bridge\EventDispatcher\Event;

class CoverageEvent extends Event
{
    const BEFORE_START   = 'doyo.coverage.start.pre';
    const BEFORE_STOP    = 'doyo.coverage.stop.pre';
    const BEFORE_REFRESH = 'doyo.coverage.refresh.pre';
    const START          = 'doyo.coverage.start';
    const STOP           = 'doyo.coverage.stop';
    const REFRESH        = 'doyo.coverage.refresh';
    const COMPLETED      = 'doyo.coverage.completed';

    /**
     * @var TestCase
     */
    private $testCase;

    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @var ConsoleIO
     */
    private $consoleIO;

    public function __construct(ProcessorInterface $processor, ConsoleIO $consoleIO, TestCase $testCase = null)
    {
        $this->processor = $processor;
        $this->testCase  = $testCase;
        $this->consoleIO = $consoleIO;
    }

    /**
     * @return TestCase
     */
    public function getTestCase(): TestCase
    {
        return $this->testCase;
    }

    /**
     * @return ProcessorInterface
     */
    public function getProcessor(): ProcessorInterface
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
