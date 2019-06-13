<?php

/*
 * This file is part of the doyo/code-coverage project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doyo\Behat\CodeCoverage\Listener;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\Tester\Result\TestResult;
use Doyo\Bridge\CodeCoverage\CodeCoverageInterface;
use Doyo\Bridge\CodeCoverage\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CoverageListener implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    private $enabled = false;

    /**
     * @var CodeCoverageInterface
     */
    private $coverage;

    public function __construct(
        CodeCoverageInterface $coverage,
        bool $enabled
    ) {
        $this->coverage = $coverage;
        $this->enabled  = $enabled;
    }

    public static function getSubscribedEvents()
    {
        return [
            ExerciseCompleted::BEFORE => 'refresh',
            ScenarioTested::BEFORE    => 'start',
            ExampleTested::BEFORE     => 'start',
            ScenarioTested::AFTER     => 'stop',
            ExampleTested::AFTER      => 'stop',
            ExerciseCompleted::AFTER  => 'complete',
        ];
    }

    public function refresh()
    {
        if (!$this->enabled) {
            return;
        }

        $this->coverage->refresh();
    }

    public function start(ScenarioTested $scenarioTested)
    {
        if (!$this->enabled) {
            return;
        }

        $scenario   = $scenarioTested->getScenario();
        $id         = $scenarioTested->getFeature()->getFile().':'.$scenario->getLine();
        $testCase   = new TestCase($id);

        $this->coverage->start($testCase);
    }

    public function stop(AfterTested $tested)
    {
        if (!$this->enabled) {
            return;
        }

        $map           = [
            TestResult::PASSED  => TestCase::RESULT_PASSED,
            TestResult::FAILED  => TestCase::RESULT_FAILED,
            TestResult::SKIPPED => TestCase::RESULT_SKIPPED,
            TestResult::PENDING => TestCase::RESULT_SKIPPED,
        ];
        $result   = $map[$tested->getTestResult()->getResultCode()];
        $coverage = $this->coverage;
        $coverage->setResult($result);
        $coverage->stop();
    }

    public function complete()
    {
        if (!$this->enabled) {
            return;
        }

        $this->coverage->complete();
    }
}
