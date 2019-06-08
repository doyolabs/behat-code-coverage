<?php

namespace spec\Doyo\Behat\Coverage\Event;

use Doyo\Behat\Coverage\Bridge\Aggregate;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Webmozart\Assert\Assert;

class CoverageEventSpec extends ObjectBehavior
{
    function let(TestCase $testCase)
    {
        $this->beConstructedWith($testCase);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CoverageEvent::class);
    }

    function its_test_case_should_be_mutable(
        TestCase $testCase
    )
    {
        $this->setTestCase(null);
        $this->getTestCase()->shouldReturn(null);
        $this->setTestCase($testCase);
        $this->getTestCase()->shouldReturn($testCase);
    }

    function its_processor_should_be_mutable(
        Processor $processor
    )
    {
        $this->getProcessor()->shouldReturn(null);
        $this->setProcessor($processor);
        $this->getProcessor()->shouldReturn($processor);
    }
}
