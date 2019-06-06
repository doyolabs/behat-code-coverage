<?php

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage;

use Behat\Mink\Driver\DriverInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use PhpSpec\ObjectBehavior;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Driver\Driver;

class ProcessorSpec extends ObjectBehavior
{
    function let(
        Driver $driver
    )
    {
        $filter = new Filter();
        $filter->addFileToWhitelist(__FILE__);
        $this->beConstructedWith($driver, $filter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Processor::class);
    }

    function its_stop_should_be_callable(
        DriverInterface $driver,
        TestCase $testCase
    )
    {
        $testCase->getName()->shouldBeCalled()->willReturn('some-id');
        $driver->start(true)->shouldBeCalled();
        $driver->stop()->shouldBeCalled()->willReturn([]);
        $this->start($testCase);
        $this->stop();
    }
}
