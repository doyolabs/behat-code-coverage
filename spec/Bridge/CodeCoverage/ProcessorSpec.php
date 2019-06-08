<?php

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage;

use Behat\Mink\Driver\DriverInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Driver\Dummy;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Bridge\Exception\ProcessorException;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\TestResult;
use SebastianBergmann\CodeCoverage\CodeCoverage;
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

    function its_code_coverage_should_be_mutable(
        Dummy $driver
    )
    {
        $codeCoverage = new CodeCoverage($driver->getWrappedObject());
        $this->getCodeCoverage()->shouldHaveType(CodeCoverage::class);
        $this->setCodeCoverage($codeCoverage);
        $this->getCodeCoverage()->shouldReturn($codeCoverage);
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

    function it_should_patch_coverage_data_when_test_completed(
        TestCase $testCase
    )
    {
        $testCase->getName()->willReturn('some-test');
        $testCase->getResult()->willReturn(TestCase::RESULT_PASSED);

        $this->addTestCase($testCase);
        $this->complete();

        $coverage = $this->getCodeCoverage();
        $coverage->getTests()->shouldHaveKey('some-test');
        $coverage->getTests()->shouldHaveKeyWithValue('some-test',['status' => TestCase::RESULT_PASSED]);
    }
}
