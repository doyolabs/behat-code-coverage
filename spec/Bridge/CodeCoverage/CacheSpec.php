<?php

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Cache;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SebastianBergmann\CodeCoverage\Filter;
use spec\Doyo\Behat\Coverage\CoverageHelperTrait;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class CacheSpec extends ObjectBehavior
{
    use CoverageHelperTrait;

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

    function its_coverage_id_should_be_mutable()
    {
        $id = 'some-id';
        $this->setCoverageId($id)->shouldReturn($this);
        $this->getCoverageId()->shouldReturn($id);
    }

    function its_coverage_should_be_mutable()
    {
        $value = ['some'];
        $this->getCoverage()->shouldReturn([]);
        $this->setCoverage($value)->shouldReturn($this);
        $this->getCoverage()->shouldReturn($value);
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

    function it_should_create_and_reset_cache()
    {
        $id = 'some-id';
        $coverage = ['data'];

        $this->setCoverageId($id);
        $this->setCoverage($coverage);
        $this->save();

        $this->readCache();
        $this->getCoverageId()->shouldReturn($id);
        $this->getCoverage()->shouldReturn($coverage);

        $this->reset();

        $this->getCoverageId()->shouldBeNull();
        $this->getCoverage()->shouldEqual([]);
    }

    function its_filter_should_be_mutable()
    {
        $filter = ['some-filter'];
        $this->getFilter()->shouldReturn([]);
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

    function it_should_start_coverage(
        $driver
    )
    {
        $id = 'some-id';
        $this->getDriverSubject($driver);

        $driver->start(true)->shouldBeCalledOnce();
        $driver->stop()->shouldBeCalledOnce()->willReturn([]);
        $this->setCoverageId($id);
        $this->save();

        $this->startCoverage($driver);
        $this->shutdown();
    }
}
