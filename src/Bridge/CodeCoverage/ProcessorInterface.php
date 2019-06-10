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
use SebastianBergmann\CodeCoverage\Filter;

interface ProcessorInterface
{
    /**
     * @return Filter
     */
    public function getCodeCoverageFilter();

    /**
     * Set code coverage options.
     *
     * @param array $options
     */
    public function setCodeCoverageOptions(array $options);

    /**
     * Get code coverage options.
     *
     * @return array
     */
    public function getCodeCoverageOptions();

    /**
     * @return CodeCoverage
     */
    public function getCodeCoverage();

    /**
     * Add test case.
     *
     * @param TestCase $testCase
     */
    public function addTestCase(TestCase $testCase);

    /**
     * Merge code coverage from another processor.
     *
     * @param ProcessorInterface|CodeCoverage $processor
     */
    public function merge($processor);

    /**
     * @param TestCase $testCase
     */
    public function start(TestCase $testCase);

    /**
     * @param bool  $append
     * @param array $linesToBeCovered
     * @param array $linesToBeUsed
     * @param bool  $ignoreForceCoversAnnotation
     *
     * @return array
     */
    public function stop(bool $append = true, $linesToBeCovered = [], array $linesToBeUsed = [], bool $ignoreForceCoversAnnotation = false): array;

    /**
     * Complete code coverage collecting process.
     */
    public function complete();

    /**
     * Clear code coverage.
     */
    public function clear();
}
