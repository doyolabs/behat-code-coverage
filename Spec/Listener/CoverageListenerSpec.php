<?php

/*
 * This file is part of the doyo/code-coverage project.
 *
 * (c) Anthonius Munthi <https://itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spec\Doyo\Behat\CodeCoverage\Listener;

use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\Tester\Result\TestResult;
use Doyo\Behat\CodeCoverage\Listener\CoverageListener;
use Doyo\Bridge\CodeCoverage\CodeCoverageInterface;
use Doyo\Bridge\CodeCoverage\TestCase;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CoverageListenerSpec extends ObjectBehavior
{
    public function let(
        CodeCoverageInterface $coverage
    ) {
        $this->beConstructedWith($coverage, true);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CoverageListener::class);
    }

    public function it_should_refresh_code_coverage(
        CodeCoverageInterface $coverage
    ) {
        $coverage->refresh()->shouldBeCalledOnce();

        $this->refresh();
    }

    public function it_should_start_code_coverage(
        CodeCoverageInterface $coverage,
        ScenarioTested $tested,
        ScenarioInterface $scenario,
        FeatureNode $feature
    ) {
        $tested->getScenario()
            ->willReturn($scenario);
        $tested->getFeature()
            ->willReturn($feature);

        $scenario->getLine()->willReturn('line');
        $feature->getFile()->willReturn('file');

        $coverage
            ->start(Argument::type(TestCase::class))
            ->shouldBeCalledOnce();

        $this->start($tested);
    }

    public function it_should_stop_code_coverage(
        AfterTested $tested,
        CodeCoverageInterface $coverage,
        TestResult $result
    ) {
        $tested->getTestResult()->willReturn($result);
        $result->getResultCode()->willReturn(0);

        $coverage->setResult(0)->shouldBeCalledOnce();
        $coverage->stop()->shouldBeCalledOnce();

        $this->stop($tested);
    }

    public function it_should_complete_code_coverage(
        CodeCoverageInterface $coverage
    ) {
        $coverage->complete()->shouldBeCalledOnce();

        $this->complete();
    }
}
