<?php

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Cache;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Bridge\Exception\CacheException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Filter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Webmozart\Assert\Assert;
use SebastianBergmann\CodeCoverage\CodeCoverage as CodeCoverage;

class CacheSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('spec-test');
        $this->reset();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Cache::class);
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

    function its_coverage_should_be_mutable()
    {
        $value = ['some'];
        $this->getData()->shouldReturn([]);
        $this->setData($value)->shouldReturn($this);
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
        $this->getNamespace()->shouldReturn('spec-test');
    }

    function it_should_create_and_reset_cache(
        TestCase $testCase
    )
    {
        $coverage = ['data'];

        $this->setTestCase($testCase);
        $this->setData($coverage);
        $this->save();

        $this->readCache();
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
        $this->setFilter($filter)->shouldReturn($this);
        $this->getFilter()->shouldReturn($filter);
    }

    function it_should_create_coverage_filter()
    {
        $this->setFilter([
            'addFilesToWhitelist' => [
                __FILE__
            ]
        ]);
        $filter = $this->createFilter();
        $filter->shouldBeAnInstanceOf(Filter::class);
        $filter->getWhitelistedFiles()->shouldHaveKeyWithValue(__FILE__, true);
    }

    function it_should_handle_error_when_starting_coverage(
        Processor $coverage,
        TestCase $testCase,
        Driver $driver
    )
    {
        $phpCoverage = new CodeCoverage($driver->getWrappedObject());
        $coverage->setCodeCoverage($phpCoverage);
        //$coverage->setAddUncoveredFilesFromWhitelist(false)->shouldBeCalled();

        /*
        $this->setCodeCoverageOptions([
            'addUncoveredFilesFromWhitelist' => false,
        ]);
        */

        $this->setCodeCoverage($coverage->getWrappedObject());
        $e = new \RuntimeException('some error');
        $coverage->start($testCase)->shouldBeCalledOnce()->willThrow($e);

        $this->setTestCase($testCase);

        $this->shouldThrow(CacheException::class)
            ->during('startCoverage', []);
    }
}
