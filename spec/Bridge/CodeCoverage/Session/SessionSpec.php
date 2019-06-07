<?php

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage\Session;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Driver\Dummy;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\Session;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use SebastianBergmann\CodeCoverage\Filter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Webmozart\Assert\Assert;
use SebastianBergmann\CodeCoverage\CodeCoverage as CodeCoverage;

class SessionSpec extends ObjectBehavior
{
    /**
     * @var CodeCoverage
     */
    private $codeCoverage;

    function let(
        Processor $processor,
        Dummy $dummy,
        TestCase $testCase
    )
    {
        $this->beAnInstanceOf(TestSession::class);
        $this->beConstructedWith('spec-test');
        $this->reset();

        $codeCoverage = new CodeCoverage($dummy->getWrappedObject());

        $testCase->getName()->willReturn('some-test');
        $this->setTestCase($testCase);

        $processor->setCodeCoverage($codeCoverage);
        $this->codeCoverage = $codeCoverage;
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

    function its_data_should_be_mutable()
    {
        $value = ['some'];
        $this->getData()->shouldReturn([]);
        $this->setData($value);
        $this->getData()->shouldReturn($value);
    }

    function its_code_coverage_options_should_be_mutable()
    {
        $option = ['some-option'];
        $this->setCodeCoverageOptions($option)->shouldReturn($this);
        $this->getCodeCoverageOptions()->shouldReturn($option);
    }

    function its_cache_adapter_should_be_mutable(
        FilesystemAdapter $adapter
    )
    {
        $this->getAdapter()->shouldHaveType(FilesystemAdapter::class);
        $this->setAdapter($adapter)->shouldReturn($this);
        $this->getAdapter()->shouldReturn($adapter);
    }

    function its_namespace_should_be_mutable()
    {
        $this->getName()->shouldReturn('spec-test');
    }

    function it_should_create_and_reset_cache(
        TestCase $testCase
    )
    {
        $coverage = ['data'];

        $this->setTestCase($testCase);
        $this->setData($coverage);
        $this->save();

        $this->refresh();
        $this->getTestCase()->shouldHaveType(TestCase::class);
        $this->getData()->shouldReturn($coverage);

        $this->reset();

        $this->getTestCase()->shouldBeNull();
        $this->getData()->shouldEqual([]);
    }

    function its_filter_should_be_mutable()
    {
        $filter = ['some-filter'];
        $this->reset();
        $this->setFilterOptions($filter)->shouldReturn($this);
        $this->getFilterOptions()->shouldReturn($filter);
    }

    function it_should_create_processor_when_not_exist(
        Dummy $driver
    )
    {
        $this->setFilterOptions([
            'whitelistedFiles' => [
                __FILE__ => true,
            ]
        ]);
        $this->setCodeCoverageOptions([
            'addUncoveredFilesFromWhitelist' => true
        ]);
        $driver->start(Argument::any())->shouldBeCalled();
        $this->setProcessor(null);
        $this->start($driver);

        //$this->getProcessor()->getCodeCoverage()->setAddUncoveredFilesFromWhitelist(true)

        $this->getProcessor()->shouldBeAnInstanceOf(Processor::class);
        $filter = $this->getProcessor()->filter();
        $filter->shouldBeAnInstanceOf(Filter::class);
        $filter->getWhitelistedFiles()->shouldHaveKeyWithValue(__FILE__, true);

        $this->shutdown();
    }

    function it_should_not_start_process_without_test_case(
        Processor $processor
    )
    {
        $processor->start(Argument::any())->shouldNotBeCalled();
        $this->setTestCase(null);

        $this->start();
    }

    function it_should_handle_error_when_starting_coverage(
        Processor $processor,
        TestCase $testCase
    )
    {
        $e = new \RuntimeException('some error');
        $this->setTestCase($testCase);

        $processor->start($testCase)->shouldBeCalledOnce()->willThrow($e);
        $this->setProcessor($processor);


        $this->start();
        $this->hasExceptions()->shouldBe(true);
    }

    function it_should_stop_code_coverage_on_shutdown(
        Processor $processor,
        TestCase $testCase
    )
    {
        $processor->start($testCase)->shouldBeCalledOnce();
        $processor->stop()->shouldBeCalledOnce();

        $this->start();
        $this->shutdown();
    }

    function it_should_handle_exception_during_shutdown(
        Processor $processor,
        TestCase $testCase
    )
    {
        $this->reset();
        $e = new \Exception('some error');
        $processor->start($testCase)->shouldBeCalled();
        $processor->stop()->willThrow($e);
        $this->setTestCase($testCase);
        $this->setProcessor($processor);

        $this->start();

        $this->shutdown();
        $this->hasExceptions()->shouldBe(true);
    }
}
