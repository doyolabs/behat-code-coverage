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

namespace Doyo\Behat\Coverage\Bridge\CodeCoverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Filter;

/**
 * Provide bridge to PHP Code Coverage.
 *
 * @method        append(array $data, $id = null, $append = true, $linesToBeCovered = [], array $linesToBeUsed = [], $ignoreForceCoversAnnotation = false)
 * @method        setAddUncoveredFilesFromWhitelist(bool $flag)
 * @method        clear()
 * @method array  getTests()
 * @method Driver getDriver()
 * @method Filter filter()
 * @method array  stop(bool $append = true, $linesToBeCovered = [], array $linesToBeUsed = [], bool $ignoreForceCoversAnnotation = false)
 */
class Processor
{
    /**
     * @var CodeCoverage
     */
    private $codeCoverage;

    /**
     * @var TestCase[]
     */
    private $testCases;

    private $completed = false;

    public function __construct($driver = null, $filter = null)
    {
        $codeCoverage       = new CodeCoverage($driver, $filter);
        $this->codeCoverage = $codeCoverage;
    }

    public function setCodeCoverage(self $codeCoverage)
    {
        $this->codeCoverage = $codeCoverage;
    }

    /**
     * @return CodeCoverage
     */
    public function getCodeCoverage()
    {
        return $this->codeCoverage;
    }

    public function addTestCase(TestCase $testCase)
    {
        $this->testCases[$testCase->getName()] = $testCase;
    }

    public function complete()
    {
        $coverage  = $this->codeCoverage;
        $testCases = $this->testCases;
        $tests     = $coverage->getTests();

        foreach ($testCases as $testCase) {
            $name                   = $testCase->getName();
            $tests[$name]['status'] = $testCase->getResult();
        }

        $coverage->setTests($tests);
        $this->completed = true;
    }

    public function start(TestCase $testCase, $clear = false)
    {
        $this->codeCoverage->start($testCase->getName(), $clear);
    }

    public function __call($name, $arguments)
    {
        $codeCoverage = $this->codeCoverage;
        if (method_exists($codeCoverage, $name)) {
            return \call_user_func_array([$codeCoverage, $name], $arguments);
        }
        throw new \RuntimeException('Method name: '.$name.' not supported.');
    }
}
