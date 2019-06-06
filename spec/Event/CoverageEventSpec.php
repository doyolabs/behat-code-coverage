<?php

namespace spec\Doyo\Behat\Coverage\Event;

use Doyo\Behat\Coverage\Bridge\Aggregate;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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

    function its_coverage_should_be_mutable()
    {
        $data = ['some-data'];
        $this->getCoverage()->shouldBeArray();
        $this->setCoverage($data);
        $this->getCoverage()->shouldReturn($data);
    }

    function its_test_case_should_be_mutable(
        TestCase $testCase
    )
    {
        $this->getTestCase()->shouldReturn($testCase);
    }


}
