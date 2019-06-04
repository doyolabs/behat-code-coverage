<?php

namespace spec\Doyo\Behat\Coverage\Bridge;

use Doyo\Behat\Coverage\Bridge\Compat;
use Doyo\Behat\Coverage\Bridge\ProxyCoverage;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Event\ReportEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SebastianBergmann\CodeCoverage\CodeCoverage;

class ProxyCoverageSpec extends ObjectBehavior
{
    function let($driver)
    {
        $driver->beADoubleOf(Compat::getDriverClass('Dummy'));
        $coverage = new CodeCoverage($driver->getWrappedObject());
        $this->beConstructedWith($coverage);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProxyCoverage::class);
    }

    function it_should_subscribe_to_coverage_event()
    {
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(CoverageEvent::START,'onCoverageStarted');
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(CoverageEvent::STOP, ['onCoverageStopped',10]);
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(CoverageEvent::REFRESH, 'onCoverageRefresh');
    }

    function it_should_subscribe_to_report_event()
    {
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(ReportEvent::BEFORE_PROCESS, 'onBeforeReport');
    }
}
