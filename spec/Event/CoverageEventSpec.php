<?php

namespace spec\Doyo\Behat\Coverage\Event;

use Doyo\Behat\Coverage\Bridge\Aggregate;
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
        $this->setTestCase(null);
        $this->getTestCase()->shouldReturn(null);
        $this->setTestCase($testCase);
        $this->getTestCase()->shouldReturn($testCase);
    }

    function its_updateCoverage_should_merge_coverage_data()
    {
        $data = [
            __FILE__ => [
                10 => -4,
                11 => -4,
                12 => -4,
            ]
        ];
        $this->updateCoverage($data);
        $this->getCoverage()->shouldReturn($data);

        $data = [
            __FILE__ => [
                10 => -1,
                11 => -2,
                12 => 2,
            ]
        ];

        $expected = [
            __FILE__ => [
                10 => -1,
                11 => -2,
                12 => 1,
            ]
        ];
        $this->updateCoverage($data);
        $result = $this->getCoverage()->getWrappedObject();
        Assert::eq($result, $expected);

    }

}
