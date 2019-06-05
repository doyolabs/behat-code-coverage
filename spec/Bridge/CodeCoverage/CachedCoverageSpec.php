<?php

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\CachedCoverage;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter;
use spec\Doyo\Behat\Coverage\CoverageHelperTrait;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

class CachedCoverageSpec extends ObjectBehavior
{
    use CoverageHelperTrait;

    function let()
    {
        $this->beConstructedWith('spec-test');
        $this->initialize();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CachedCoverage::class);
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

        $ob = new CachedCoverage('spec-test');
        Assert::eq($ob->getCoverageId(), $id);
        Assert::same($ob->getCoverage(), $coverage);
        $ob->initialize();

        $ob = new CachedCoverage('spec-test');
        Assert::null($ob->getCoverageId());
        Assert::isEmpty($ob->getCoverage());
    }

    function it_should_subscribe_to_coverage_events()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::REFRESH);
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::START);
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::STOP);
    }

    function it_should_handle_coverage_refresh_event()
    {
        $this->setCoverage(['data']);
        $this->save();

        $this->onCoverageRefresh();
        $this->readCache();
        $this->getCoverage()->shouldBeEqualTo([]);
    }

    function it_should_handle_coverage_start_event(
        CoverageEvent $event
    )
    {
        $id = 'coverage-id';
        $event->getCoverageId()->shouldBeCalledOnce()->willReturn($id);

        $this->onCoverageStarted($event);
        $this->readCache();
        $this->getCoverageId()->shouldBeEqualTo('coverage-id');
    }

    function it_should_handle_coverage_stop_event(
        CoverageEvent $event
    )
    {
        $data = ['onCoverageStopped'];
        $this->setCoverage($data);
        $this->save();

        $event->updateCoverage($data)->shouldBeCalledOnce();

        $this->onCoverageStopped($event);
        $this->getCoverage()->shouldBeEqualTo($data);
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
        $driver->stop()->shouldBeCalledOnce();
        $this->setCoverageId($id);
        $this->startCoverage($driver);
        $this->shutdown();
    }
}
