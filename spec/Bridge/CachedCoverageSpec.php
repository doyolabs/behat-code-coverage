<?php

namespace spec\Doyo\Behat\Coverage\Bridge;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Cache;
use Doyo\Behat\Coverage\Bridge\CachedCoverage;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Bridge\Exception\CacheException;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SebastianBergmann\CodeCoverage\Filter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CachedCoverageSpec extends ObjectBehavior
{
    function let(
        Cache $cache
    )
    {
        $filter = new Filter();
        $this->beConstructedWith('spec-test', [], $filter);
        $this->setCache($cache);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CachedCoverage::class);
    }

    function it_should_subscribe_to_coverage_events()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::REFRESH);
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::START);
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::STOP);
    }

    function its_cache_should_be_mutable(
        Cache $cache
    )
    {
        $this->setCache($cache)->shouldReturn($this);
        $this->getCache()->shouldReturn($cache);
    }

    function it_should_handle_coverage_refresh_event(
        Cache $cache
    )
    {
        $cache->reset()->shouldBeCalledOnce();
        $this->onCoverageRefresh();
    }

    function it_should_handle_coverage_start_event(
        Cache $cache,
        CoverageEvent $event,
        TestCase $testCase
    )
    {
        $event->getTestCase()->shouldBeCalledOnce()->willReturn($testCase);
        $cache->setTestCase($testCase)->shouldBeCalledOnce();
        $cache->save()->shouldBeCalledOnce();

        $this->onCoverageStarted($event);
    }

    function it_should_handle_on_coverage_stop_event(
        CoverageEvent $event,
        Cache $cache
    )
    {
        $data = ['onCoverageStop'];
        $cache->readCache()->shouldBeCalled();
        $cache->getData()->shouldBeCalled()->willReturn($data);
        $cache->hasExceptions()->willReturn(false);
        $event->updateCoverage($data)->shouldBeCalled();

        $this->onCoverageStopped($event);

        $cache->hasExceptions()->willReturn(true);
        $cache->getExceptions()->willReturn(['Test Message']);

        $this
            ->shouldThrow(CacheException::class)
            ->during('onCoverageStopped',[$event]);
    }

}
