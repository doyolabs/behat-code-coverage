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

use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Bridge\Symfony\Event;

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
     * @var \Exception[]
     */
    private $exceptions;

    public function __construct(TestCase $testCase = null)
    {
        $this->testCase   = $testCase;
    }

    /**
     * @return TestCase
     */
    public function getTestCase()
    {
        return $this->testCase;
    }

    /**
     * @param TestCase $testCase
     */
    public function setTestCase($testCase=null)
    {
        $this->testCase = $testCase;
    }

    public function setProcessor($processor)
    {
        $this->processor = $processor;
    }

    public function getProcessor()
    {
        return $this->processor;
    }

    public function addException(\Exception $exception)
    {
        $id = md5($exception->getMessage());

        if (!isset($this->exceptions[$id])) {
            $this->exceptions[$id] = $exception;
        }
    }
}
