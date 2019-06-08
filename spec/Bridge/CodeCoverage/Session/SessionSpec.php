<?php

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage\Session;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Driver\Dummy;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\Session;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use PhpSpec\ObjectBehavior;
use SebastianBergmann\CodeCoverage\Filter;
use Prophecy\Argument;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Webmozart\Assert\Assert;
use SebastianBergmann\CodeCoverage\CodeCoverage;

class SessionSpec extends ObjectBehavior
{
    function let(
        ProcessorInterface $processor
    )
    {
        $filter = new Filter();
        $this->beAnInstanceOf(TestSession::class);
        $this->beConstructedWith( 'spec-test');
        $processor->getCodeCoverageFilter()->willReturn($filter);
        $processor->getCodeCoverageOptions()->willReturn([]);
        $this->setProcessor($processor);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Session::class);
    }

    function it_should_be_serializable()
    {
        $this->shouldImplement(\Serializable::class);
    }

    function its_test_case_should_be_mutable(
        TestCase $testCase
    )
    {
        $this->setTestCase($testCase)->shouldReturn($this);
        $this->getTestCase()->shouldReturn($testCase);
    }


    function its_cache_adapter_should_be_mutable(
        FilesystemAdapter $adapter
    )
    {
        $this->getAdapter()->shouldHaveType(FilesystemAdapter::class);
        $this->setAdapter($adapter)->shouldReturn($this);
        $this->getAdapter()->shouldReturn($adapter);
    }

    function its_name_should_be_mutable()
    {
        $this->getName()->shouldReturn('spec-test');
    }

    function its_exceptions_should_be_mutable()
    {
        $exception = new \Exception('some-error');
        $this->hasExceptions()->shouldBe(false);
        $this->addException($exception);
        $this->hasExceptions()->shouldBe(true);
        $this->getExceptions()->shouldContain($exception);
    }

    function it_should_create_and_reset_session(
        TestCase $testCase,
        ProcessorInterface $processor
    )
    {
        $processor->clear()->shouldBeCalledOnce();
        $this->setProcessor($processor);
        $this->setTestCase($testCase);
        $this->save();
        $this->getTestCase()->shouldHaveType(TestCase::class);

        $this->reset();
        $this->getTestCase()->shouldBeNull();
    }

    function it_should_start_and_stop_code_coverage(
        ProcessorInterface $processor,
        Dummy $driver,
        TestCase $testCase
    )
    {
        $testCase->getName()->shouldBeCalledOnce()->willReturn('some-test');
        $processor->merge(Argument::type(CodeCoverage::class))
            ->shouldBeCalled();
        $driver->start(Argument::any())->shouldBeCalledOnce();
        $driver->stop()->shouldBeCalledOnce()->willReturn([]);

        $this->setProcessor($processor);
        $this->setTestCase($testCase);
        $this->start($driver);
        $this->stop();
        $this->hasExceptions()->shouldBe(false);
    }
}
