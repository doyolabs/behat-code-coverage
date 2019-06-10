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

namespace Doyo\Behat\Coverage\Bridge\CodeCoverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Filter;

/**
 * Provide bridge to PHP Code Coverage.
 */
class Processor implements ProcessorInterface
{
    /**
     * @var CodeCoverage
     */
    private $codeCoverage;

    /**
     * @var TestCase[]
     */
    private $testCases = [];

    private $completed = false;

    /**
     * @var Driver
     */
    private $driver;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var array
     */
    private $coverageOptions;

    /**
     * @var TestCase
     */
    private $currentTestCase;

    public function __construct($driver = null, $filter = null)
    {
        $this->driver = $driver;
        $this->filter = $filter;
    }

    public function setCurrentTestCase(TestCase $testCase)
    {
        $this->currentTestCase = $testCase;
    }

    public function getCurrentTestCase()
    {
        return $this->currentTestCase;
    }

    public function setCodeCoverageOptions(array $options)
    {
        $this->coverageOptions = $options;
    }

    public function getCodeCoverageOptions()
    {
        return $this->coverageOptions;
    }

    public function getCodeCoverageFilter()
    {
        return $this->filter;
    }

    public function start(TestCase $testCase, $clear = false)
    {
        $this->setCurrentTestCase($testCase);
        $this->addTestCase($testCase);
        $this->getCodeCoverage()->start($testCase->getName(), $clear);
    }

    public function stop(bool $append = true, $linesToBeCovered = [], array $linesToBeUsed = [], bool $ignoreForceCoversAnnotation = false): array
    {
        return $this->getCodeCoverage()->stop($append, $linesToBeCovered, $linesToBeUsed, $ignoreForceCoversAnnotation);
    }

    public function merge($processor)
    {
        $codeCoverage = $processor;
        if ($processor instanceof self) {
            $codeCoverage = $processor->getCodeCoverage();
        }
        $this->getCodeCoverage()->merge($codeCoverage);
    }

    public function clear()
    {
        $this->getCodeCoverage()->clear();
    }

    public function setCodeCoverage(CodeCoverage $codeCoverage)
    {
        $this->codeCoverage = $codeCoverage;
    }

    /**
     * @return CodeCoverage
     */
    public function getCodeCoverage()
    {
        if (null === $this->codeCoverage) {
            $this->codeCoverage = new CodeCoverage($this->driver, $this->filter);
        }

        return $this->codeCoverage;
    }

    public function addTestCase(TestCase $testCase)
    {
        $this->testCases[$testCase->getName()] = $testCase;
    }

    public function complete()
    {
        $coverage  = $this->getCodeCoverage();
        $testCases = $this->testCases;
        $tests     = $coverage->getTests();

        foreach ($testCases as $testCase) {
            $name                   = $testCase->getName();
            $tests[$name]['status'] = $testCase->getResult();
        }

        $coverage->setTests($tests);
        $this->completed = true;
    }
}
